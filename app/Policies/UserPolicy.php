<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** Jika UserPolicy sudah ada dari Tahap 1, gabungkan method-nya, jangan ditimpa. */
class UserPolicy
{
    private function isAdmin(User $user): bool
    {
        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        return $role === UserRole::ADMIN;
    }

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, User $target): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $target): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, User $target): bool
    {
        return $this->isAdmin($user) && $user->id !== $target->id;
    }
}
