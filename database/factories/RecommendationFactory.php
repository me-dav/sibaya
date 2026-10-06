<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Recommendation, Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Recommendation> */
class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'material_id' => Material::factory(),
            'reason' => 'Nilai paling rendah pada materi ini.',
            'is_read' => false,
        ];
    }
}
