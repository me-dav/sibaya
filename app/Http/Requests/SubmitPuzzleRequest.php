<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Hanya jawaban. Skor tidak pernah diterima dari browser. */
class SubmitPuzzleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // kepemilikan dicek PuzzleAttemptPolicy
    }

    public function rules(): array
    {
        return [
            'items'   => ['nullable', 'array', 'max:30'],
            'items.*' => ['nullable', 'string', 'max:40'],
            'cells'   => ['nullable', 'array', 'max:700'],
            'cells.*' => ['nullable', 'string', 'max:4'],
        ];
    }
}
