<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{EvaluationAttempt, Certificate, Evaluation, Exercise, ExerciseAttempt, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<EvaluationAttempt> */
class EvaluationAttemptFactory extends Factory
{
    protected $model = EvaluationAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'evaluation_id' => Evaluation::factory(),
            'started_at' => now(),
            'status' => EvaluationAttemptStatus::IN_PROGRESS,
        ];
    }
}
