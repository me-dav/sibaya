<?php

namespace App\Http\Requests;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Material::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $slugSource = filled($this->input('slug')) ? $this->input('slug') : (string) $this->input('title');

        $this->merge([
            'slug'         => Str::slug($slugSource),
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        /** @var Material|null $material */
        $material = $this->route('material');

        return [
            // Satu jenis Bahasa Rinengga hanya punya satu materi utama (tanpa submateri).
            'rinengga_type_id' => ['required', 'integer', Rule::exists('rinengga_types', 'id'),
                Rule::unique('materials', 'rinengga_type_id')->ignore($material?->id)],
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'max:255', Rule::unique('materials', 'slug')->ignore($material?->id)],
            'description'  => ['required', 'string', 'max:1000'],
            'content'      => ['required', 'string'],
            'examples'     => ['nullable', 'string'],
            'summary'      => ['nullable', 'string'],
            'video_url'    => ['nullable', 'url:https', 'max:500'],
            'is_published' => ['boolean'],
            'image'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'audio'        => ['nullable', 'file', 'mimes:mp3,wav,ogg', 'max:10240'],
            'remove_image' => ['sometimes', 'boolean'],
            'remove_audio' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'rinengga_type_id.unique' => 'Jenis Basa Rinengga iki wis duwe materi utama. (Setiap jenis hanya boleh punya satu materi.)',
            'slug.unique'             => 'Slug wis dipakai materi liyane.',
            'video_url.url'           => 'URL video kudu diwiwiti nganggo https://',
        ];
    }

    public function attributes(): array
    {
        return [
            'rinengga_type_id' => 'jenis Basa Rinengga',
            'title'            => 'judul',
            'description'      => 'deskripsi',
            'content'          => 'isi materi',
            'examples'         => 'tuladha',
            'summary'          => 'rangkuman',
            'image'            => 'gambar',
            'audio'            => 'audio',
        ];
    }
}
