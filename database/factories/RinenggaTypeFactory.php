<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{RinenggaType, Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Material, Question, QuestionOption, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<RinenggaType> */
class RinenggaTypeFactory extends Factory
{
    protected $model = RinenggaType::class;

    public function definition(): array
    {
        return [
            'name' => $name = fake()->unique()->words(2, true),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_published' => true,
        ];
    }
}
