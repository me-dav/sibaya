<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Exercise, Certificate, Evaluation, EvaluationAttempt, ExerciseAttempt, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Exercise> */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'title' => $t = fake()->unique()->sentence(3),
            'slug' => Str::slug($t),
            'description' => fake()->sentence(),
            'exercise_type' => ExerciseType::PEMAHAMAN,
            'rinengga_type_id' => null,
            'is_randomized' => false,
            'is_published' => true,
        ];
    }
}
