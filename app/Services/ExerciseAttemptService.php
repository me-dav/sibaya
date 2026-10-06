<?php

namespace App\Services;

use App\Enums\AttemptStatus;
use App\Models\Exercise;
use App\Models\ExerciseAnswer;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use App\Models\User;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseAttemptService
{
    public function __construct(private AnswerEvaluationService $evaluator) {}

    public function start(User $user, Exercise $exercise): ExerciseAttempt
    {
        if (! $exercise->questions()->exists()) {
            throw new DomainException('Latihan ini durung duwe soal. (Latihan ini belum memiliki soal.)');
        }

        return ExerciseAttempt::firstOrCreate(
            [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'status' => AttemptStatus::IN_PROGRESS->value,
            ],
            ['started_at' => now()]
        );
    }

    /**
     * Soal untuk satu attempt, lengkap dengan opsi.
     * Jika latihan diacak: urutan ditentukan oleh id attempt (deterministik),
     * jadi reload halaman tidak mengubah urutan, attempt baru urutannya berbeda,
     * dan data master tidak disentuh. Opsi jawaban tidak diacak.
     */
    public function questionsFor(ExerciseAttempt $attempt): Collection
    {
        $attempt->loadMissing('exercise');

        $questions = Question::where('exercise_id', $attempt->exercise_id)
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->applyOrder($attempt, $questions, fn (Question $q) => $q->id);
    }

    /** Jawaban dalam urutan yang sama dengan yang dilihat siswa. */
    public function answersFor(ExerciseAttempt $attempt): Collection
    {
        $attempt->loadMissing('exercise');

        $answers = $attempt->answers()
            ->with(['question.options', 'selectedOption'])
            ->orderBy('id')
            ->get();

        return $this->applyOrder($attempt, $answers, fn (ExerciseAnswer $a) => $a->question_id);
    }

    /**
     * Submit dan nilai. Idempotent: attempt yang sudah selesai tidak diubah lagi.
     *
     * @param  array<int|string, mixed>  $answers  question_id => jawaban mentah (lihat AnswerEvaluationService)
     */
    public function submit(ExerciseAttempt $attempt, array $answers): ExerciseAttempt
    {
        return DB::transaction(function () use ($attempt, $answers) {
            $attempt = ExerciseAttempt::whereKey($attempt->getKey())->lockForUpdate()->firstOrFail();

            if ($attempt->status !== AttemptStatus::IN_PROGRESS) {
                return $attempt;
            }

            $questions = Question::where('exercise_id', $attempt->exercise_id)
                ->with('options')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $totalPoints = 0;
            $earned = 0;
            $correct = 0;
            $wrong = 0;

            foreach ($questions as $question) {
                $result = $this->evaluator->evaluate($question, $answers[$question->id] ?? null);

                ExerciseAnswer::create([
                    'exercise_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_option_id' => $result->selectedOptionId,
                    'answer_text' => $result->answerText,
                    'is_correct' => $result->isCorrect,
                    'points_earned' => $result->pointsEarned,
                ]);

                $totalPoints += $question->maxPoints();
                $earned += $result->pointsEarned;
                $result->isCorrect ? $correct++ : $wrong++;
            }

            $attempt->update([
                'score' => $totalPoints > 0 ? round($earned / $totalPoints * 100, 2) : 0,
                'total_points' => $totalPoints,
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'status' => AttemptStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            return $attempt;
        });
    }

    public function summaryFor(User $user): array
    {
        $base = ExerciseAttempt::where('user_id', $user->id)
            ->where('status', AttemptStatus::COMPLETED->value);

        return [
            'completed_exercises' => (clone $base)->distinct()->count('exercise_id'),
            'total_exercises' => Exercise::published()->count(),
            'best_score' => (clone $base)->max('score'),
        ];
    }

    private function applyOrder(ExerciseAttempt $attempt, Collection $items, callable $questionId): Collection
    {
        if (! $attempt->exercise->is_randomized) {
            return $items->values();
        }

        return $items
            ->sortBy(fn ($item) => sprintf('%010d-%010d', crc32($attempt->id.'-'.$questionId($item)), $questionId($item)))
            ->values();
    }
}
