<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{QuestionOption, Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Material, Question, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<QuestionOption> */
class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'option_key' => 'A',
            'option_text' => fake()->sentence(3),
            'is_correct' => false,
            'sort_order' => 0,
        ];
    }
}
