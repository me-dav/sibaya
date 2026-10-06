<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Exercise;
use App\Models\User;

/** Jika Tahap 5 sudah membuat ExercisePolicy, gabungkan method-nya, jangan ditimpa. */
class ExercisePolicy
{
    private function isAdmin(?User $user): bool
    {
        $role = $user?->role;
        $role = $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role);

        return $role === UserRole::ADMIN;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Exercise $exercise): bool
    {
        return $exercise->is_published || $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Exercise $exercise): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Exercise $exercise): bool
    {
        return $this->isAdmin($user);
    }
}
