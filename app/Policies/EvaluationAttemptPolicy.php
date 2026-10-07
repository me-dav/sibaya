<?php

namespace App\Policies;

use App\Models\EvaluationAttempt;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Proteksi IDOR: attempt milik siswa lain dijawab 404, bukan 403. */
class EvaluationAttemptPolicy
{
    public function view(User $user, EvaluationAttempt $attempt): Response|bool
    {
        return $user->id === $attempt->user_id ? true : Response::denyAsNotFound();
    }

    public function answer(User $user, EvaluationAttempt $attempt): Response|bool
    {
        return $this->view($user, $attempt);
    }
}
