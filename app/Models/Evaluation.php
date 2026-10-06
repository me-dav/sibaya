<?php

namespace App\Models;

use App\Enums\EvaluationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'type', 'description', 'instructions', 'duration_minutes',
        'passing_score', 'max_attempts', 'start_at', 'end_at', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => EvaluationType::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_published' => 'boolean',
            'duration_minutes' => 'integer',
            'passing_score' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'evaluation_questions')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function evaluationQuestions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(EvaluationAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
