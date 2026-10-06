<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Collection;

/** Statistik dasar dashboard admin (tanpa analitik kompleks, sesuai CLAUDE.md bagian 33). */
class AdminStatisticsService
{
    public function summary(): array
    {
        $students = fn () => User::query()->where('role', UserRole::SISWA->value);

        return [
            'total_students'      => $students()->count(),
            'active_students'     => $students()->where('is_active', true)->count(),
            'total_materials'     => Material::count(),
            'total_exercises'     => Exercise::count(),
            'total_evaluations'   => Evaluation::count(),
            'exercise_attempts'   => ExerciseAttempt::count(),
            'evaluation_attempts' => EvaluationAttempt::count(),
            'avg_exercise'        => ExerciseAttempt::where('status', 'completed')->avg('score'),
            'avg_evaluation'      => EvaluationAttempt::where('status', 'completed')->avg('score'),
        ];
    }

    public function latestEvaluations(int $limit = 5): Collection
    {
        return Evaluation::query()->latest()->limit($limit)->get();
    }

    /** Gabungan hasil latihan dan evaluasi terbaru, diurutkan menurut waktu. */
    public function recentResults(int $limit = 8): Collection
    {
        $exercise = ExerciseAttempt::query()->with(['user', 'exercise'])
            ->where('status', 'completed')->latest('completed_at')->limit($limit)->get()
            ->map(fn ($a) => [
                'kind' => 'Latihan', 'student' => $a->user?->name, 'title' => $a->exercise?->title,
                'score' => $a->score, 'at' => $a->completed_at,
                'url' => route('admin.results.exercises.show', $a),
            ]);

        $evaluation = EvaluationAttempt::query()->with(['user', 'evaluation'])
            ->where('status', 'completed')->latest('submitted_at')->limit($limit)->get()
            ->map(fn ($a) => [
                'kind' => 'Evaluasi', 'student' => $a->user?->name, 'title' => $a->evaluation?->title,
                'score' => $a->score, 'at' => $a->submitted_at, 'url' => null,
            ]);

        return $exercise->concat($evaluation)->sortByDesc('at')->take($limit)->values();
    }
}
