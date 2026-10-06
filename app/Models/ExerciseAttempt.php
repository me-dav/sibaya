<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'exercise_id', 'started_at', 'completed_at', 'score',
        'total_points', 'correct_answers', 'wrong_answers', 'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'decimal:2',
            'status' => AttemptStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExerciseAnswer::class);
    }
}
