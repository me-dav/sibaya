<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\User;
use App\Support\Labels;

/** Data dashboard siswa. Semua data dibatasi pada siswa yang sedang login. */
class StudentDashboardService
{
    public function __construct(private readonly RecommendationService $recommendations)
    {
    }

    public function data(User $user): array
    {
        // --- evaluasi
        $attempts = EvaluationAttempt::where('user_id', $user->id)->latest('started_at')->get();
        $evaluations = Evaluation::whereIn('id', $attempts->pluck('evaluation_id')->unique())->get()->keyBy('id');
        $finished = $attempts->whereIn('status', ['completed', 'expired']);

        $best = function (string $type) use ($finished, $evaluations) {
            $attempt = $finished->filter(fn ($a) => Labels::value($evaluations->get($a->evaluation_id)?->type) === $type)
                ->sortByDesc('score')->first();

            return $attempt ? [
                'score'  => $attempt->score,
                'passed' => $attempt->score >= ($evaluations->get($attempt->evaluation_id)->passing_score ?? 0),
                'title'  => $evaluations->get($attempt->evaluation_id)->title,
                'attempt' => $attempt,
            ] : null;
        };

        // --- latihan
        $exerciseAttempts = ExerciseAttempt::where('user_id', $user->id)->where('status', 'completed')
            ->latest('completed_at')->limit(8)->get();
        $exerciseTitles = Exercise::whereIn('id', $exerciseAttempts->pluck('exercise_id')->unique())->pluck('title', 'id');

        return [
            'uts'              => $best('uts'),
            'uas'              => $best('uas'),
            'history'          => $attempts->take(10),
            'evaluations'      => $evaluations,
            'lastExercise'     => $exerciseAttempts->first(),
            'exerciseAttempts' => $exerciseAttempts,
            'exerciseTitles'   => $exerciseTitles,
            'exerciseTotal'    => ExerciseAttempt::where('user_id', $user->id)->where('status', 'completed')->count(),
            'recommendations'  => $this->recommendations->summary($user),
            'certificates'     => app(CertificateService::class)->availableFor($user),
        ];
    }
}
