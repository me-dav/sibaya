<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Puzzle;
use App\Models\User;

class PuzzlePolicy
{
    private function role(?User $user): ?UserRole
    {
        $role = $user?->role;

        return $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role);
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Puzzle $puzzle): bool
    {
        return $puzzle->is_published || $this->role($user) === UserRole::ADMIN;
    }

    /** Hanya siswa aktif yang bermain (riwayat dan skor admin tidak mengotori data siswa). */
    public function play(User $user, Puzzle $puzzle): bool
    {
        return $this->role($user) === UserRole::SISWA && (bool) $user->is_active && (bool) $puzzle->is_published;
    }

    public function create(User $user): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }

    public function update(User $user, Puzzle $puzzle): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }

    public function delete(User $user, Puzzle $puzzle): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }
}
