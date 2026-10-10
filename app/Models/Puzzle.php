<?php

namespace App\Models;

use App\Enums\PuzzleType;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'puzzle_type', 'difficulty', 'instructions', 'configuration', 'is_published'];

    protected function casts(): array
    {
        return [
            'puzzle_type'   => PuzzleType::class,
            'configuration' => 'array',
            'is_published'  => 'boolean',
        ];
    }

    public function attempts()
    {
        return $this->hasMany(PuzzleAttempt::class);
    }
}
