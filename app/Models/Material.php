<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'rinengga_type_id', 'title', 'slug', 'description', 'content', 'examples',
        'summary', 'image_path', 'audio_path', 'video_url', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function rinenggaType(): BelongsTo
    {
        return $this->belongsTo(RinenggaType::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /** Siswa hanya boleh melihat materi published. */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
