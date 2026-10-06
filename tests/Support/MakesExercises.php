<?php

namespace Tests\Support;

use App\Models\Exercise;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

trait MakesExercises
{
    protected function makeExercise(string $type = 'pemahaman', array $attrs = []): Exercise
    {
        $e = new Exercise();
        $e->forceFill(array_merge([
            'title' => 'Latihan '.$type, 'slug' => 'latihan-'.$type.'-'.uniqid(),
            'exercise_type' => $type, 'is_randomized' => false, 'is_published' => false,
        ], $attrs))->save();

        return $e;
    }

    protected function makeQuestion(Exercise $e, array $attrs = []): Question
    {
        $q = new Question();
        $q->forceFill(array_merge([
            'exercise_id' => $e->id, 'question_type' => 'short_answer', 'question_text' => 'Apa?',
            'answer_key' => 'jawab', 'points' => 1, 'sort_order' => 1,
        ], $attrs))->save();

        return $q;
    }

    protected function makeAttempt(int $userId, int $exerciseId, array $attrs = []): int
    {
        return DB::table('exercise_attempts')->insertGetId(array_merge([
            'user_id' => $userId, 'exercise_id' => $exerciseId, 'started_at' => now(), 'completed_at' => now(),
            'score' => 80, 'total_points' => 10, 'correct_answers' => 4, 'wrong_answers' => 1,
            'status' => 'completed', 'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }
}
