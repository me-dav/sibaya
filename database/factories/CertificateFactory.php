<?php

namespace Database\Factories;

use App\Enums\{AttemptStatus, EvaluationAttemptStatus, EvaluationType, ExerciseType, QuestionType};
use App\Models\{Certificate, Evaluation, EvaluationAttempt, Exercise, ExerciseAttempt, Material, Question, QuestionOption, RinenggaType, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Certificate> */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'evaluation_attempt_id' => EvaluationAttempt::factory(),
            'certificate_number' => 'SIBAYA-' . strtoupper(Str::random(8)),
            'score' => 90,
            'issued_at' => now(),
        ];
    }
}
