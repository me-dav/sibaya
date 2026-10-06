<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    protected function passwordRules(): array
    {
        return ['required', 'confirmed', Password::defaults()];
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->id)],
            'password'    => $this->passwordRules(),
            'class_name'  => ['required', 'string', 'max:100'],
            'school_name' => ['required', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'jeneng', 'class_name' => 'kelas', 'school_name' => 'sekolah', 'password' => 'sandi'];
    }
}
