<?php

namespace App\Http\Requests;

use App\Support\Labels;

/** Jenis puzzle tidak dapat diubah setelah dibuat (struktur konfigurasi berbeda). */
class UpdatePuzzleRequest extends StorePuzzleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('puzzle')) ?? false;
    }

    protected function isCreate(): bool
    {
        return false;
    }

    protected function puzzleType(): string
    {
        return (string) Labels::value($this->route('puzzle')->puzzle_type);
    }
}
