<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id', 'question_type', 'question_text', 'passage_text', 'audio_path',
        'answer_key', 'keywords', 'points', 'sort_order', 'metadata',
    ];

    /**
     * Kunci jawaban tidak ikut serialisasi. metadata disembunyikan karena
     * analysis_rows berisi kata kunci. View siswa memakai analysisLabels().
     */
    protected $hidden = ['answer_key', 'keywords', 'metadata'];

    protected function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'keywords' => 'array',
            'metadata' => 'array',
            'points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** Poin maksimal soal ini. Analisis teks = jumlah poin tiap baris. */
    public function maxPoints(): int
    {
        if ($this->question_type === QuestionType::TEXT_ANALYSIS) {
            return (int) collect($this->metadata['analysis_rows'] ?? [])->sum('points');
        }

        return (int) $this->points;
    }

    /** Label baris analisis (aman ditampilkan ke siswa; tanpa kata kunci). */
    public function analysisLabels(): array
    {
        return collect($this->metadata['analysis_rows'] ?? [])->pluck('label')->values()->all();
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order')->orderBy('option_key');
    }

    public function exerciseAnswers(): HasMany
    {
        return $this->hasMany(ExerciseAnswer::class);
    }

    public function evaluationQuestions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class);
    }

    public function evaluationAnswers(): HasMany
    {
        return $this->hasMany(EvaluationAnswer::class);
    }
}
