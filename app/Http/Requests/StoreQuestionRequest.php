<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Exercise;
use App\Support\Labels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->exercise()) ?? false;
    }

    protected function exercise(): Exercise
    {
        return $this->route('exercise');
    }

    protected function isCreate(): bool
    {
        return true;
    }

    /** Tipe soal yang boleh dibuat. Default: menurut jenis latihan. */
    protected function allowedTypes(): array
    {
        return Labels::allowedQuestionTypes(Labels::value($this->exercise()->exercise_type));
    }

    protected function questionType(): string
    {
        return (string) $this->input('question_type');
    }

    protected function audioRequired(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->questionType();
        $rules = ['rinengga_type_id' => ['nullable', 'integer', Rule::exists('rinengga_types', 'id')]];

        if ($this->isCreate()) {
            $rules['question_type'] = ['required', Rule::enum(QuestionType::class), Rule::in($this->allowedTypes())];
        }

        $options = [
            'option_a'       => ['required', 'string', 'max:500'],
            'option_b'       => ['required', 'string', 'max:500'],
            'option_c'       => ['nullable', 'string', 'max:500'],
            'option_d'       => ['nullable', 'string', 'max:500'],
            'correct_option' => ['required', 'in:A,B,C,D'],
            'points'         => ['required', 'integer', 'min:1', 'max:1000'],
        ];

        switch ($type) {
            case 'multiple_choice':
                $rules += ['question_text' => ['required', 'string', 'max:5000']] + $options;
                break;

            case 'audio_choice':
                $rules += ['question_text' => ['required', 'string', 'max:5000']] + $options;
                $rules['audio_file'] = [$this->audioRequired() ? 'required' : 'nullable', 'file', 'mimes:mp3,wav,ogg', 'max:10240'];
                break;

            case 'text_analysis':
                $rules += [
                    'question_text'               => ['nullable', 'string', 'max:5000'],
                    'passage_text'                => ['required', 'string'],
                    'analysis_rows'               => ['required', 'array', 'min:1', 'max:20'],
                    'analysis_rows.*.label'       => ['required', 'string', 'max:100'],
                    'analysis_rows.*.keywords'    => ['required', 'string', 'max:1000'],
                    'analysis_rows.*.points'      => ['required', 'integer', 'min:1', 'max:1000'],
                ];
                break;

            case 'short_answer':
                $rules += [
                    'question_text' => ['required', 'string', 'max:5000'],
                    'answer_key'    => ['required', 'string', 'max:1000'],
                    'keywords'      => ['nullable', 'string', 'max:2000'],
                    'points'        => ['required', 'integer', 'min:1', 'max:1000'],
                ];
                break;
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if (in_array($this->questionType(), ['multiple_choice', 'audio_choice'], true)) {
                $key = strtolower((string) $this->input('correct_option'));
                if ($key !== '' && blank($this->input('option_'.$key))) {
                    $v->errors()->add('correct_option', 'Kunci jawaban kudu nuding opsi sing ana isine.');
                }
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'question_text' => 'pertanyaan', 'passage_text' => 'teks', 'option_a' => 'opsi A', 'option_b' => 'opsi B',
            'option_c' => 'opsi C', 'option_d' => 'opsi D', 'correct_option' => 'kunci jawaban', 'points' => 'bobot',
            'answer_key' => 'jawaban bener', 'audio_file' => 'audio', 'analysis_rows' => 'baris analisis',
            'analysis_rows.*.label' => 'label baris', 'analysis_rows.*.keywords' => 'kata kunci', 'analysis_rows.*.points' => 'poin baris',
        ];
    }
}
