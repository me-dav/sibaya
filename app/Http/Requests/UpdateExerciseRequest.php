<?php

namespace App\Http\Requests;

use App\Support\Labels;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateExerciseRequest extends StoreExerciseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('exercise')) ?? false;
    }

    public function after(): array
    {
        return [function (Validator $v) {
            $exercise = $this->route('exercise');
            $existing = DB::table('questions')->where('exercise_id', $exercise->id)->pluck('question_type')->unique();

            // Jenis latihan tidak boleh diubah jika soal yang ada tidak cocok dengan jenis baru.
            $allowed = Labels::allowedQuestionTypes((string) $this->input('exercise_type'));
            if ($existing->contains(fn ($t) => ! in_array($t, $allowed, true))) {
                $v->errors()->add('exercise_type', 'Jenis latihan ora bisa diowahi amarga ana soal sing ora cocog karo jenis iki.');
            }

            // Latihan kosong tidak boleh diterbitkan.
            if ($this->boolean('is_published') && $existing->isEmpty()) {
                $v->errors()->add('is_published', 'Latihan durung duwe soal, mula durung bisa diterbitake.');
            }
        }];
    }
}
