<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Pemilik atau admin. Sertifikat milik siswa lain dijawab 404 (proteksi IDOR). */
class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): Response|bool
    {
        $role = $user->role instanceof UserRole ? $user->role : UserRole::tryFrom((string) $user->role);

        if ($role === UserRole::ADMIN || $user->id === $certificate->user_id) {
            return true;
        }

        return Response::denyAsNotFound();
    }
}
