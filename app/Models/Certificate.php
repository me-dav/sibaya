<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'evaluation_attempt_id', 'certificate_number',
        'score', 'predicate', 'pdf_path', 'issued_at',
    ];

    protected function casts(): array
    {
        return ['score' => 'decimal:2', 'issued_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluationAttempt(): BelongsTo
    {
        return $this->belongsTo(EvaluationAttempt::class);
    }
}
