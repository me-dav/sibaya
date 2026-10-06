<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
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

    /** Tamu dan siswa hanya boleh melihat materi published dari jenis yang published. */
    public function view(?User $user, Material $material): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $material->is_published && (bool) $material->rinenggaType?->is_published;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Material $material): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Material $material): bool
    {
        return $this->isAdmin($user);
    }
}
