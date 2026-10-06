<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'material_id', 'source_evaluation_attempt_id', 'reason', 'is_read'];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function sourceEvaluationAttempt(): BelongsTo
    {
        return $this->belongsTo(EvaluationAttempt::class, 'source_evaluation_attempt_id');
    }
}
