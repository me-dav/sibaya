<?php

namespace App\Models;

use App\Enums\ExerciseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'exercise_type', 'rinengga_type_id',
        'instructions', 'is_randomized', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'exercise_type' => ExerciseType::class,
            'is_randomized' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function rinenggaType(): BelongsTo
    {
        return $this->belongsTo(RinenggaType::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
