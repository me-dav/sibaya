<?php

namespace App\Http\Requests;

use App\Support\EvaluationRules;
use App\Support\Labels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachEvaluationQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('evaluation')) ?? false;
    }

    public function rules(): array
    {
        $type = EvaluationRules::questionType((string) Labels::value($this->route('evaluation')->type));

        return [
            'question_ids'   => ['required', 'array', 'min:1', 'max:100'],
            'question_ids.*' => ['integer', Rule::exists('questions', 'id')->where('question_type', $type)],
        ];
    }

    public function messages(): array
    {
        return [
            'question_ids.required'  => 'Pilih paling ora siji soal.',
            'question_ids.*.exists'  => 'Ana soal sing tipene ora cocog karo jenis evaluasi iki.',
        ];
    }
}
