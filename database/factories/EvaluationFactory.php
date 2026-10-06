<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Evaluation, Certificate, EvaluationAttempt, Exercise, ExerciseAttempt, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Evaluation> */
class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => EvaluationType::UTS,
            'duration_minutes' => 45,
            'passing_score' => 75,
            'max_attempts' => 1,
            'is_published' => true,
        ];
    }

    public function uas(): static
    {
        return $this->state(fn () => ['type' => EvaluationType::UAS]);
    }
}
