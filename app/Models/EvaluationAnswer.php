<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationAnswer extends Model
{


    protected $fillable = [
        'evaluation_attempt_id', 'question_id', 'selected_option_id',
        'answer_text', 'is_correct', 'points_earned',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean', 'points_earned' => 'decimal:2'];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(EvaluationAttempt::class, 'evaluation_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }
}
