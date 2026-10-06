<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExerciseRequest extends FormRequest
{
    private const MAX_TEXT = 500;

    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt !== null && $this->user()?->can('view', $attempt) === true;
    }

    /**
     * Bentuk jawaban:
     *  answers[question_id]            = id opsi atau teks isian
     *  answers[question_id][row_index] = teks (analisis)
     */
    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array', 'max:200'],
            'answers.*' => ['nullable', function (string $attribute, mixed $value, \Closure $fail) {
                $tooLong = fn ($v) => is_string($v) && mb_strlen($v) > self::MAX_TEXT;
                $scalarOk = fn ($v) => $v === null || is_string($v) || is_int($v);

                if (is_array($value)) {
                    if (count($value) > 50) {
                        $fail('Jawaban kakehan. (Terlalu banyak baris jawaban.)');
                    }
                    foreach ($value as $v) {
                        if (! $scalarOk($v) || $tooLong($v)) {
                            $fail('Format jawaban ora valid.');
                            return;
                        }
                    }
                    return;
                }

                if (! $scalarOk($value) || $tooLong($value)) {
                    $fail('Jawaban kedawan, maksimal '.self::MAX_TEXT.' karakter.');
                }
            }],
        ];
    }
}
