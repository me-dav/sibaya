<?php

namespace App\Http\Requests;

use App\Enums\EvaluationType;
use App\Models\Evaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Evaluation::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['required', Rule::enum(EvaluationType::class)],
            'description'      => ['nullable', 'string', 'max:2000'],
            'instructions'     => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'passing_score'    => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts'     => ['required', 'integer', 'min:1', 'max:20'],
            'start_at'         => ['nullable', 'date'],
            'end_at'           => ['nullable', 'date', Rule::when($this->filled('start_at'), ['after:start_at'])],
            'is_published'     => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul', 'type' => 'jenis evaluasi', 'duration_minutes' => 'durasi', 'passing_score' => 'nilai kelulusan',
            'max_attempts' => 'maksimum percobaan', 'start_at' => 'wektu wiwit', 'end_at' => 'wektu rampung',
        ];
    }
}
