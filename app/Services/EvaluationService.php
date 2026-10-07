<?php

namespace App\Services;

use App\Exceptions\EvaluationNotAvailable;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationAttempt;
use App\Models\Question;
use App\Models\User;
use App\Support\Labels;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Alur pengerjaan evaluasi: mulai, simpan jawaban, submit, penilaian, dan kedaluwarsa.
 * Semua keputusan waktu dan nilai diambil di server (CLAUDE.md bagian 27-29).
 */
class EvaluationService
{
    /** Toleransi jaringan untuk submit otomatis saat timer klien habis. */
    public const GRACE_SECONDS = 3;

    public function __construct(
        private readonly AnswerEvaluationService $grader,
        private readonly EvaluationBuilderService $builder,
    ) {
    }

    // ---------------------------------------------------------------- waktu

    private function carbon(mixed $v): ?Carbon
    {
        return $v ? Carbon::parse($v) : null;
    }

    public function deadline(EvaluationAttempt $attempt, ?Evaluation $evaluation = null): Carbon
    {
        $evaluation ??= Evaluation::findOrFail($attempt->evaluation_id);

        return $this->carbon($attempt->started_at)->copy()->addMinutes((int) $evaluation->duration_minutes);
    }

    // ---------------------------------------------------------------- ketersediaan

    /** @return array{can: bool, reason: ?string, open: ?EvaluationAttempt} */
    public function availability(?User $user, Evaluation $e): array
    {
        try {
            $this->assertPublished($e);

            if ($user) {
                $open = $this->openAttempt($user, $e);
                if ($open && $this->finalizeIfExpired($open)->status === 'in_progress') {
                    return ['can' => true, 'reason' => null, 'open' => $open->fresh()];
                }
            }

            $this->assertWindow($e);
            $this->assertHasQuestions($e);
            if ($user) {
                $this->assertAttemptsLeft($user, $e);
            }

            return ['can' => true, 'reason' => null, 'open' => null];
        } catch (EvaluationNotAvailable $ex) {
            return ['can' => false, 'reason' => $ex->getMessage(), 'open' => null];
        }
    }

    private function assertPublished(Evaluation $e): void
    {
        if (! $e->is_published) {
            throw new EvaluationNotAvailable('Evaluasi iki durung diterbitake.');
        }
    }

    private function assertWindow(Evaluation $e): void
    {
        $now = now();
        $start = $this->carbon($e->start_at);
        $end = $this->carbon($e->end_at);

        if ($start && $now->lt($start)) {
            throw new EvaluationNotAvailable('Evaluasi durung dibuka. Diwiwiti '.$start->translatedFormat('d M Y H:i').'.');
        }
        if ($end && $now->gt($end)) {
            throw new EvaluationNotAvailable('Wektu evaluasi wis ditutup.');
        }
    }

    private function assertHasQuestions(Evaluation $e): void
    {
        $stats = $this->questionStats()->get($e->id);

        if (! $stats || (int) $stats->n === 0 || (float) $stats->pts <= 0) {
            throw new EvaluationNotAvailable('Evaluasi iki durung duwe soal.');
        }
    }

    private function assertAttemptsLeft(User $user, Evaluation $e): void
    {
        $used = EvaluationAttempt::where('user_id', $user->id)->where('evaluation_id', $e->id)->count();

        if ($used >= (int) $e->max_attempts) {
            throw new EvaluationNotAvailable("Kesempatan ngerjakake wis entek ({$used}/{$e->max_attempts}).");
        }
    }

    /** evaluation_id => {n: jumlah soal, pts: total poin} */
    public function questionStats(): Collection
    {
        return DB::table('evaluation_questions as eq')
            ->join('questions as q', 'q.id', '=', 'eq.question_id')
            ->selectRaw('eq.evaluation_id as evaluation_id, count(*) as n, sum(q.points) as pts')
            ->groupBy('eq.evaluation_id')->get()->keyBy('evaluation_id');
    }

    public function openAttempt(User $user, Evaluation $e): ?EvaluationAttempt
    {
        return EvaluationAttempt::where('user_id', $user->id)->where('evaluation_id', $e->id)
            ->where('status', 'in_progress')->latest('id')->first();
    }

    // ---------------------------------------------------------------- mulai

    public function start(User $user, Evaluation $evaluation): EvaluationAttempt
    {
        return DB::transaction(function () use ($user, $evaluation) {
            // Kunci baris evaluasi supaya dua klik "Mulai" bersamaan tidak membuat dua attempt.
            $evaluation = Evaluation::whereKey($evaluation->id)->lockForUpdate()->firstOrFail();

            $this->assertPublished($evaluation);

            if ($open = $this->openAttempt($user, $evaluation)) {
                $open = $this->finalizeIfExpired($open);
                if ($open->status === 'in_progress') {
                    return $open;
                }
            }

            $this->assertWindow($evaluation);
            $this->assertHasQuestions($evaluation);
            $this->assertAttemptsLeft($user, $evaluation);

            $attempt = new EvaluationAttempt();
            $attempt->forceFill([
                'user_id' => $user->id, 'evaluation_id' => $evaluation->id, 'started_at' => now(),
                'score' => 0, 'total_points' => 0, 'correct_answers' => 0, 'wrong_answers' => 0, 'status' => 'in_progress',
            ])->save();

            return $attempt;
        });
    }

    // ---------------------------------------------------------------- jawaban

    /** Simpan sebagian jawaban (autosave). Menolak jika waktu habis. */
    public function saveAnswers(EvaluationAttempt $attempt, array $answers): void
    {
        $outcome = DB::transaction(function () use ($attempt, $answers) {
            $attempt = EvaluationAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($attempt->status !== 'in_progress') {
                return 'finished';
            }

            if (now()->gt($this->deadline($attempt)->copy()->addSeconds(self::GRACE_SECONDS))) {
                $this->finalize($attempt, 'expired'); // harus ter-commit, jadi exception dilempar SETELAH transaksi
                return 'expired';
            }

            $this->storeAnswers($attempt, $answers);

            return 'saved';
        });

        if ($outcome === 'finished') {
            throw new EvaluationNotAvailable('Evaluasi wis rampung.');
        }
        if ($outcome === 'expired') {
            throw new EvaluationNotAvailable('Wektu entek.');
        }
    }

    public function submit(EvaluationAttempt $attempt, array $answers = []): EvaluationAttempt
    {
        return DB::transaction(function () use ($attempt, $answers) {
            $attempt = EvaluationAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($attempt->status !== 'in_progress') {
                return $attempt; // idempotent: submit ganda tidak menghitung ulang
            }

            $deadline = $this->deadline($attempt);
            $now = now();

            // Jawaban yang datang setelah waktu habis (di luar toleransi) diabaikan.
            if ($now->lte($deadline->copy()->addSeconds(self::GRACE_SECONDS))) {
                $this->storeAnswers($attempt, $answers);
            }

            return $this->finalize($attempt, $now->gt($deadline) ? 'expired' : 'completed');
        });
    }

    /** Tandai attempt yang waktunya lewat sebagai expired dan nilai jawaban yang sudah tersimpan. */
    public function finalizeIfExpired(EvaluationAttempt $attempt): EvaluationAttempt
    {
        if ($attempt->status !== 'in_progress') {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt) {
            $attempt = EvaluationAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($attempt->status === 'in_progress'
                && now()->gt($this->deadline($attempt)->copy()->addSeconds(self::GRACE_SECONDS))) {
                return $this->finalize($attempt, 'expired');
            }

            return $attempt;
        });
    }

    private function storeAnswers(EvaluationAttempt $attempt, array $answers): void
    {
        $questions = $this->builder->questionsOf($attempt->evaluation_id)->keyBy('id');

        foreach ($answers as $questionId => $value) {
            $question = $questions->get((int) $questionId);
            if (! $question) {
                continue; // soal bukan bagian evaluasi ini
            }

            $selected = null;
            $text = null;

            if (Labels::value($question->question_type) === 'multiple_choice') {
                $option = filled($value) ? $question->options->firstWhere('id', (int) $value) : null;
                $selected = $option?->id;
            } else {
                $text = filled($value) ? mb_substr(trim((string) $value), 0, 2000) : null;
            }

            $row = EvaluationAnswer::where('evaluation_attempt_id', $attempt->id)->where('question_id', $question->id)->first()
                ?? new EvaluationAnswer();

            $row->forceFill([
                'evaluation_attempt_id' => $attempt->id, 'question_id' => $question->id,
                'selected_option_id' => $selected, 'answer_text' => $text,
            ])->save();
        }
    }

    // ---------------------------------------------------------------- penilaian

    private function finalize(EvaluationAttempt $attempt, string $status): EvaluationAttempt
    {
        $evaluation = Evaluation::findOrFail($attempt->evaluation_id);
        $questions = $this->builder->questionsOf($evaluation->id);
        $answers = EvaluationAnswer::where('evaluation_attempt_id', $attempt->id)->get()->keyBy('question_id');

        $total = 0;
        $earned = 0;
        $correct = 0;

        foreach ($questions as $question) {
            $row = $answers->get($question->id) ?? new EvaluationAnswer();
            $result = $this->grade($question, $row);

            $row->forceFill([
                'evaluation_attempt_id' => $attempt->id, 'question_id' => $question->id,
                'is_correct' => $result['is_correct'], 'points_earned' => $result['points'],
            ])->save();

            $total += $question->points;
            $earned += $result['points'];
            $correct += $result['is_correct'] ? 1 : 0;
        }

        $now = now();
        $started = $this->carbon($attempt->started_at);
        $limit = (int) $evaluation->duration_minutes * 60;

        $attempt->forceFill([
            'submitted_at'     => $now,
            'score'            => $total > 0 ? round($earned / $total * 100, 2) : 0,
            'total_points'     => $total,
            'correct_answers'  => $correct,
            'wrong_answers'    => $questions->count() - $correct,
            'duration_seconds' => max(0, min($limit, $now->getTimestamp() - $started->getTimestamp())),
            'status'           => $status,
        ])->save();

        // Rekomendasi dibuat SETELAH nilai ter-commit; kegagalannya tidak boleh membatalkan nilai siswa.
        DB::afterCommit(function () use ($attempt) {
            try {
                app(RecommendationService::class)->generateForAttempt($attempt);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return $attempt;
    }

    /**
     * Adapter ke AnswerEvaluationService (Tahap 6). Jika signature di project berbeda,
     * cukup sesuaikan dua pemanggilan di method ini.
     *
     * @return array{is_correct: bool, points: int|float}
     */
    private function grade(Question $question, EvaluationAnswer $row): array
    {
        $type = Labels::value($question->question_type);

        // Jawaban kosong selalu 0 poin (CLAUDE.md bagian 28).
        $empty = $type === 'multiple_choice' ? blank($row->selected_option_id) : blank($row->answer_text);
        if ($empty) {
            return ['is_correct' => false, 'points' => 0];
        }

        $raw = match ($type) {
            'multiple_choice' => $this->grader->evaluateMultipleChoice($question, $row->selected_option_id),
            'short_answer'    => $this->grader->evaluateShortAnswer($question, $row->answer_text),
            default           => throw new \LogicException("Tipe soal {$type} tidak didukung pada evaluasi."),
        };

        if (is_array($raw)) {
            $points = $raw['points_earned'] ?? $raw['points'] ?? null;
            $ok = (bool) ($raw['is_correct'] ?? ((float) $points > 0));
            $points ??= $ok ? $question->points : 0;
        } elseif (is_bool($raw)) {
            $ok = $raw;
            $points = $ok ? $question->points : 0;
        } else {
            $points = (float) $raw;
            $ok = $points > 0;
        }

        $points = max(0, min((float) $points, (float) $question->points));

        return ['is_correct' => $ok, 'points' => $points == floor($points) ? (int) $points : $points];
    }
}
