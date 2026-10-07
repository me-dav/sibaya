<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Dipakai untuk autosave dan submit akhir. Nilai tidak pernah diterima dari browser. */
class SubmitEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // kepemilikan attempt dicek oleh EvaluationAttemptPolicy
    }

    public function rules(): array
    {
        return [
            'answers'   => ['nullable', 'array', 'max:200'],
            'answers.*' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
