<?php

namespace Tests\Support;

use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait MakesEvaluations
{
    protected function makeEvaluation(string $type = 'uts', array $attrs = []): Evaluation
    {
        $e = new Evaluation();
        $e->forceFill(array_merge([
            'title' => strtoupper($type).' Uji', 'type' => $type, 'duration_minutes' => 30, 'passing_score' => 75,
            'max_attempts' => 2, 'is_published' => true,
        ], $attrs))->save();

        return $e;
    }

    /** Soal pilihan ganda mandiri; opsi B benar. Mengembalikan [question, correctOption, wrongOption]. */
    protected function makeMc(Evaluation $e, int $points = 2): array
    {
        $q = new Question();
        $q->forceFill(['exercise_id' => null, 'question_type' => 'multiple_choice', 'question_text' => 'Apa iku?',
            'answer_key' => 'B', 'points' => $points, 'sort_order' => 0])->save();

        $opts = [];
        foreach (['A', 'B'] as $i => $k) {
            $o = new QuestionOption();
            $o->forceFill(['question_id' => $q->id, 'option_key' => $k, 'option_text' => "Opsi $k", 'is_correct' => $k === 'B', 'sort_order' => $i + 1])->save();
            $opts[$k] = $o;
        }

        $this->attachQuestion($e, $q);

        return [$q, $opts['B'], $opts['A']];
    }

    protected function makeShort(Evaluation $e, int $points = 5): Question
    {
        $q = new Question();
        $keywords = ['makna kias'];
        $q->forceFill(['exercise_id' => null, 'question_type' => 'short_answer', 'question_text' => 'Jelaskan',
            'answer_key' => 'makna kias', 'keywords' => $q->hasCast('keywords') ? $keywords : json_encode($keywords),
            'points' => $points, 'sort_order' => 0])->save();

        $this->attachQuestion($e, $q);

        return $q;
    }

    protected function attachQuestion(Evaluation $e, Question $q): void
    {
        DB::table('evaluation_questions')->insert([
            'evaluation_id' => $e->id, 'question_id' => $q->id,
            'sort_order' => (int) DB::table('evaluation_questions')->where('evaluation_id', $e->id)->max('sort_order') + 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function makeEvalAttempt(User $u, Evaluation $e, array $attrs = []): EvaluationAttempt
    {
        $a = new EvaluationAttempt();
        $a->forceFill(array_merge([
            'user_id' => $u->id, 'evaluation_id' => $e->id, 'started_at' => now(), 'score' => 0,
            'total_points' => 0, 'correct_answers' => 0, 'wrong_answers' => 0, 'status' => 'in_progress',
        ], $attrs))->save();

        return $a;
    }
}
