<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RinenggaType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
