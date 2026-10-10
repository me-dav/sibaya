<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 'role' sengaja TIDAK ada di $fillable agar tidak bisa di-mass-assign
     * dari request. Set role secara eksplisit di controller/seeder/service.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'class_name',
        'school_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isSiswa(): bool
    {
        return $this->role === UserRole::SISWA;
    }

    public function exerciseAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExerciseAttempt::class);
    }

    public function evaluationAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluationAttempt::class);
    }

    public function puzzleAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PuzzleAttempt::class);
    }

    public function certificates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function recommendations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}
