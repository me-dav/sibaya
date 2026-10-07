<?php

namespace App\Http\Requests;

use App\Support\Labels;

class UpdateEvaluationQuestionRequest extends StoreEvaluationQuestionRequest
{
    protected function isCreate(): bool
    {
        return false;
    }

    protected function questionType(): string
    {
        return (string) Labels::value($this->route('question')->question_type);
    }

    protected function audioRequired(): bool
    {
        return false;
    }
}
