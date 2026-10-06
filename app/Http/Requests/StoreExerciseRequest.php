<?php

namespace App\Http\Requests;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Exercise::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $source = filled($this->input('slug')) ? $this->input('slug') : (string) $this->input('title');

        $this->merge([
            'slug'          => Str::slug($source),
            'is_randomized' => $this->boolean('is_randomized'),
            'is_published'  => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        /** @var Exercise|null $exercise */
        $exercise = $this->route('exercise');

        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255', Rule::unique('exercises', 'slug')->ignore($exercise?->id)],
            'description'      => ['nullable', 'string', 'max:2000'],
            'exercise_type'    => ['required', Rule::enum(ExerciseType::class)],
            'rinengga_type_id' => ['nullable', 'integer', Rule::exists('rinengga_types', 'id')],
            'instructions'     => ['nullable', 'string', 'max:5000'],
            'is_randomized'    => ['boolean'],
            'is_published'     => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul', 'description' => 'deskripsi', 'exercise_type' => 'jenis latihan',
            'rinengga_type_id' => 'jenis Basa Rinengga', 'instructions' => 'petunjuk',
        ];
    }
}
