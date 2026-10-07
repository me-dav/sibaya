<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\Question;
use App\Support\EvaluationRules;
use App\Support\Labels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Pengelolaan daftar soal evaluasi (sisi admin). */
class EvaluationBuilderService
{
    private function type(Evaluation $e): string
    {
        return (string) Labels::value($e->type);
    }

    /** Soal evaluasi berurutan, lengkap dengan opsi. */
    public function questionsOf(int $evaluationId): Collection
    {
        $ids = DB::table('evaluation_questions')->where('evaluation_id', $evaluationId)
            ->orderBy('sort_order')->orderBy('id')->pluck('question_id')->all();

        $questions = Question::whereIn('id', $ids)
            ->with(['options' => fn ($q) => $q->orderBy('sort_order')])->get()->keyBy('id');

        return collect($ids)->map(fn ($id) => $questions->get($id))->filter()->values();
    }

    /** Bank soal: soal bertipe sesuai yang belum dipasang pada evaluasi ini. */
    public function eligibleQuestions(Evaluation $e, ?string $search = null, int $limit = 50): Collection
    {
        $attached = DB::table('evaluation_questions')->where('evaluation_id', $e->id)->pluck('question_id');
        $search = mb_strtolower(trim((string) $search));

        return Question::query()
            ->where('question_type', EvaluationRules::questionType($this->type($e)))
            ->whereNotIn('id', $attached)
            ->when($search !== '', fn ($q) => $q->whereRaw('LOWER(question_text) LIKE ?', ["%{$search}%"]))
            ->latest('id')->limit($limit)->get();
    }

    public function hasAttempts(Evaluation $e): bool
    {
        return DB::table('evaluation_attempts')->where('evaluation_id', $e->id)->exists();
    }

    public function attach(Evaluation $e, array $questionIds): int
    {
        $order = (int) DB::table('evaluation_questions')->where('evaluation_id', $e->id)->max('sort_order');
        $added = 0;

        DB::transaction(function () use ($e, $questionIds, &$order, &$added) {
            foreach (array_unique(array_map('intval', $questionIds)) as $qid) {
                $added += DB::table('evaluation_questions')->insertOrIgnore([
                    'evaluation_id' => $e->id, 'question_id' => $qid, 'sort_order' => ++$order,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return $added;
    }

    public function isAttached(Evaluation $e, Question $q): bool
    {
        return DB::table('evaluation_questions')->where('evaluation_id', $e->id)->where('question_id', $q->id)->exists();
    }

    /** Soal tetap ada di bank soal; hanya relasinya yang dilepas. */
    public function detach(Evaluation $e, Question $q): void
    {
        DB::table('evaluation_questions')->where('evaluation_id', $e->id)->where('question_id', $q->id)->delete();
    }

    public function move(Evaluation $e, Question $q, string $direction): void
    {
        $ids = DB::table('evaluation_questions')->where('evaluation_id', $e->id)->orderBy('sort_order')->orderBy('id')->pluck('question_id')->all();
        $i = array_search($q->id, $ids, true);
        $j = $direction === 'up' ? $i - 1 : $i + 1;

        if ($i === false || ! isset($ids[$j])) {
            return;
        }

        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];

        DB::transaction(function () use ($e, $ids) {
            foreach ($ids as $n => $id) {
                DB::table('evaluation_questions')->where('evaluation_id', $e->id)->where('question_id', $id)->update(['sort_order' => $n + 1]);
            }
        });
    }

    /** Alasan evaluasi belum boleh diterbitkan (kosong = boleh). */
    public function publishErrors(Evaluation $e, ?string $type = null): array
    {
        $type ??= $this->type($e);
        $questions = $this->questionsOf($e->id);
        $errors = [];

        if ($questions->isEmpty()) {
            $errors[] = 'Evaluasi durung duwe soal.';
        } elseif ($questions->sum('points') <= 0) {
            $errors[] = 'Total poin soal kudu luwih saka 0.';
        }

        $required = EvaluationRules::questionType($type);
        if ($questions->contains(fn ($q) => Labels::value($q->question_type) !== $required)) {
            $errors[] = $type === 'uts' ? 'UTS mung nampa soal pilihan ganda.' : 'UAS mung nampa soal isian.';
        }

        return $errors;
    }

    public function canDelete(Evaluation $e): bool
    {
        return ! $this->hasAttempts($e);
    }

    public function delete(Evaluation $e): void
    {
        DB::transaction(function () use ($e) {
            DB::table('evaluation_questions')->where('evaluation_id', $e->id)->delete();
            $e->delete();
        });
    }
}
