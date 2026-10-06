<?php

namespace App\Http\Requests;

class UpdateMaterialRequest extends StoreMaterialRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('material')) ?? false;
    }
}
