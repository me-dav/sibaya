<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Support\EvaluationRules;
use App\Support\Labels;

/** Soal mandiri (bank soal) yang langsung dipasang pada evaluasi. Tipe soal ditentukan jenis evaluasi. */
class StoreEvaluationQuestionRequest extends StoreQuestionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('evaluation')) ?? false;
    }

    protected function requiredType(): string
    {
        return EvaluationRules::questionType((string) Labels::value($this->route('evaluation')->type));
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['question_type' => $this->requiredType()]);
    }

    protected function exercise(): Exercise
    {
        throw new \LogicException('Soal evaluasi tidak terikat pada latihan.');
    }

    protected function allowedTypes(): array
    {
        return [$this->requiredType()];
    }
}
