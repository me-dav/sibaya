<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Evaluation;
use App\Models\User;

class EvaluationPolicy
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

    public function view(?User $user, Evaluation $evaluation): bool
    {
        return $evaluation->is_published || $this->role($user) === UserRole::ADMIN;
    }

    public function create(User $user): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $this->role($user) === UserRole::ADMIN;
    }

    /** Hanya siswa aktif yang boleh mengerjakan evaluasi yang sudah terbit. */
    public function start(User $user, Evaluation $evaluation): bool
    {
        return $this->role($user) === UserRole::SISWA && (bool) $user->is_active && (bool) $evaluation->is_published;
    }
}
