<?php

namespace App\Policies;

use App\Models\ExerciseAttempt;
use App\Models\User;

class ExerciseAttemptPolicy
{
    /** Hanya pemilik attempt (proteksi IDOR). Siswa tidak bisa melihat hasil siswa lain. */
    public function view(User $user, ExerciseAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id;
    }

    public function answer(User $user, ExerciseAttempt $attempt): bool
    {
        return $this->view($user, $attempt);
    }
}
