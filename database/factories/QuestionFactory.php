<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Question, Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Material, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'exercise_id' => null,
            'question_type' => QuestionType::MULTIPLE_CHOICE,
            'question_text' => fake()->sentence() . '?',
            'points' => 1,
            'sort_order' => 0,
        ];
    }

    /** Pilihan ganda lengkap dengan 4 opsi; opsi A benar. */
    public function withOptions(string $correct = 'A'): static
    {
        return $this->afterCreating(function (Question $q) use ($correct) {
            foreach (['A', 'B', 'C', 'D'] as $i => $key) {
                QuestionOption::factory()->create([
                    'question_id' => $q->id,
                    'option_key' => $key,
                    'is_correct' => $key === $correct,
                    'sort_order' => $i,
                ]);
            }
        });
    }

    public function shortAnswer(array $keywords = ['makna kias']): static
    {
        return $this->state(fn () => [
            'question_type' => QuestionType::SHORT_ANSWER,
            'answer_key' => $keywords[0],
            'keywords' => $keywords,
        ]);
    }
}
