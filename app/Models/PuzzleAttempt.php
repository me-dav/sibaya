<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuzzleAttempt extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score'        => 'float',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function puzzle()
    {
        return $this->belongsTo(Puzzle::class);
    }
}
