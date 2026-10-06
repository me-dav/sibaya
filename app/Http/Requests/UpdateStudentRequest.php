<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

class UpdateStudentRequest extends StoreStudentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('student')) ?? false;
    }

    /** Sandi opsional saat mengubah data siswa. */
    protected function passwordRules(): array
    {
        return ['nullable', 'confirmed', Password::defaults()];
    }
}
