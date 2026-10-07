<?php

namespace App\Policies;

use App\Models\PuzzleAttempt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Proteksi IDOR: percobaan milik siswa lain dijawab 404. */
class PuzzleAttemptPolicy
{
    public function view(User $user, PuzzleAttempt $attempt): Response|bool
    {
        return $user->id === $attempt->user_id ? true : Response::denyAsNotFound();
    }

    public function answer(User $user, PuzzleAttempt $attempt): Response|bool
    {
        return $this->view($user, $attempt);
    }
}
