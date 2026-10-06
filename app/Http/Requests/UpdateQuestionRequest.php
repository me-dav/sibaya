<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Support\Labels;

/** Tipe soal tidak dapat diubah setelah dibuat, supaya riwayat jawaban tetap konsisten. */
class UpdateQuestionRequest extends StoreQuestionRequest
{
    protected function exercise(): Exercise
    {
        return Exercise::findOrFail($this->route('question')->exercise_id);
    }

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
        return blank($this->route('question')->audio_path);
    }
}
