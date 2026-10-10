<?php

namespace App\Models;

use App\Enums\EvaluationAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvaluationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'evaluation_id', 'started_at', 'submitted_at', 'score',
        'total_points', 'correct_answers', 'wrong_answers', 'duration_seconds', 'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    public function setStatusAttribute(mixed $value): void
    {
        $this->attributes['status'] = $value instanceof \BackedEnum ? $value->value : (string) $value;
    }

    public function statusEnum(): ?EvaluationAttemptStatus
    {
        return EvaluationAttemptStatus::tryFrom((string) $this->status);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
