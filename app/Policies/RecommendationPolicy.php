<?php

namespace App\Policies;

use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Rekomendasi milik siswa lain dijawab 404 (proteksi IDOR). */
class RecommendationPolicy
{
    public function view(User $user, Recommendation $recommendation): Response|bool
    {
        return $user->id === $recommendation->user_id ? true : Response::denyAsNotFound();
    }
}
