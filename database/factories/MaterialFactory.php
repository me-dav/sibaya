<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Material, Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Material> */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'rinengga_type_id' => RinenggaType::factory(),
            'title' => $t = fake()->unique()->sentence(3),
            'slug' => Str::slug($t),
            'description' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'examples' => fake()->sentence(),
            'summary' => fake()->sentence(),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
