<?php

namespace App\Http\Requests;

use App\Enums\PuzzleType;
use App\Models\Puzzle;
use App\Support\Crossword;
use App\Support\PuzzleRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePuzzleRequest extends FormRequest
{
    private const WORD = ['required', 'string', 'regex:/^\p{L}{2,20}$/u'];

    public function authorize(): bool
    {
        return $this->user()?->can('create', Puzzle::class) ?? false;
    }

    protected function isCreate(): bool
    {
        return true;
    }

    protected function puzzleType(): string
    {
        return (string) $this->input('puzzle_type');
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'is_published' => $this->boolean('is_published'),
            'slug'         => Str::slug(filled($this->input('slug')) ? $this->input('slug') : (string) $this->input('title')),
        ];

        // Jawaban dinormalisasi: tanpa spasi, huruf besar.
        foreach (['items', 'words'] as $key) {
            if (is_array($this->input($key))) {
                $merge[$key] = collect($this->input($key))->map(function ($row) {
                    if (is_array($row) && isset($row['answer'])) {
                        $row['answer'] = mb_strtoupper(preg_replace('/\s+/u', '', (string) $row['answer']));
                    }

                    return $row;
                })->values()->all();
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $puzzle = $this->route('puzzle');

        $rules = [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255', Rule::unique('puzzles', 'slug')->ignore($puzzle?->id)],
            'description'      => ['nullable', 'string', 'max:2000'],
            'difficulty'       => ['nullable', Rule::in(array_keys(PuzzleRules::difficulties()))],
            'instructions'     => ['nullable', 'string', 'max:5000'],
            'duration_seconds' => ['nullable', 'integer', 'min:30', 'max:7200'],
            'is_published'     => ['boolean'],
        ];

        if ($this->isCreate()) {
            $rules['puzzle_type'] = ['required', Rule::enum(PuzzleType::class)];
        }

        if ($this->puzzleType() === 'susun_kata') {
            $rules += [
                'items'          => ['required', 'array', 'min:1', 'max:30'],
                'items.*.clue'   => ['required', 'string', 'max:500'],
                'items.*.answer' => self::WORD,
                'items.*.points' => ['required', 'integer', 'min:1', 'max:100'],
            ];
        } elseif ($this->puzzleType() === 'teka_teki_silang') {
            $rules += [
                'grid_rows'         => ['required', 'integer', 'min:3', 'max:25'],
                'grid_cols'         => ['required', 'integer', 'min:3', 'max:25'],
                'words'             => ['required', 'array', 'min:1', 'max:40'],
                'words.*.answer'    => self::WORD,
                'words.*.clue'      => ['required', 'string', 'max:500'],
                'words.*.direction' => ['required', 'in:across,down'],
                'words.*.row'       => ['required', 'integer', 'min:1', 'max:25'],
                'words.*.col'       => ['required', 'integer', 'min:1', 'max:25'],
                'words.*.points'    => ['required', 'integer', 'min:1', 'max:100'],
            ];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($this->puzzleType() !== 'teka_teki_silang') {
                return;
            }

            $broken = collect($v->errors()->keys())->contains(fn ($k) => str_starts_with($k, 'words') || str_starts_with($k, 'grid_'));
            if ($broken) {
                return;
            }

            $layout = Crossword::layout(['rows' => $this->input('grid_rows'), 'cols' => $this->input('grid_cols'), 'words' => $this->input('words')]);
            foreach ($layout['errors'] as $msg) {
                $v->errors()->add('words', $msg);
            }
        }];
    }

    public function messages(): array
    {
        return [
            'items.*.answer.regex' => 'Jawaban kudu mung huruf (2-20 huruf, tanpa spasi utawa angka).',
            'words.*.answer.regex' => 'Jawaban kudu mung huruf (2-20 huruf, tanpa spasi utawa angka).',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul', 'puzzle_type' => 'jenis puzzle', 'difficulty' => 'tingkat kesulitan', 'duration_seconds' => 'durasi',
            'items.*.clue' => 'petunjuk', 'items.*.answer' => 'jawaban', 'items.*.points' => 'poin',
            'words.*.clue' => 'petunjuk', 'words.*.answer' => 'jawaban', 'words.*.row' => 'baris', 'words.*.col' => 'kolom', 'words.*.points' => 'poin',
            'grid_rows' => 'jumlah baris', 'grid_cols' => 'jumlah kolom',
        ];
    }
}
