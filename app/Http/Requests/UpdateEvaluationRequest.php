<?php

namespace App\Http\Requests;

use App\Services\EvaluationBuilderService;
use App\Support\Labels;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdateEvaluationRequest extends StoreEvaluationRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('evaluation')) ?? false;
    }

    public function after(): array
    {
        return [function (Validator $v) {
            $evaluation = $this->route('evaluation');
            $builder = app(EvaluationBuilderService::class);
            $newType = (string) $this->input('type');

            // Jenis evaluasi terkunci setelah ada soal atau attempt.
            if ($newType !== (string) Labels::value($evaluation->type)) {
                $hasQuestions = DB::table('evaluation_questions')->where('evaluation_id', $evaluation->id)->exists();
                if ($hasQuestions || $builder->hasAttempts($evaluation)) {
                    $v->errors()->add('type', 'Jenis evaluasi ora bisa diowahi amarga wis ana soal utawa percobaan siswa.');
                }
            }

            if ($this->boolean('is_published')) {
                foreach ($builder->publishErrors($evaluation, $newType) as $msg) {
                    $v->errors()->add('is_published', $msg);
                }
            }
        }];
    }
}
