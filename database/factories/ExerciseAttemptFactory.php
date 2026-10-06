<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{ExerciseAttempt, Certificate, Evaluation, EvaluationAttempt, Exercise, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ExerciseAttempt> */
class ExerciseAttemptFactory extends Factory
{
    protected $model = ExerciseAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exercise_id' => Exercise::factory(),
            'started_at' => now(),
            'status' => AttemptStatus::IN_PROGRESS,
        ];
    }
}
