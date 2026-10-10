<?php

namespace App\Http\Controllers;

use App\Enums\AttemptStatus;
use App\Http\Requests\SubmitExerciseRequest;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Services\AnswerEvaluationService;
use App\Services\ExerciseAttemptService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExerciseAttemptController extends Controller
{
    public function __construct(private ExerciseAttemptService $service) {}

    public function start(Request $request, Exercise $exercise): RedirectResponse
    {
        abort_unless($exercise->is_published, 404);

        try {
            $attempt = $this->service->start($request->user(), $exercise);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('exercises.attempt', $attempt);
    }

    /** Form pengerjaan. Kunci jawaban tidak dikirim; baris analisis hanya lewat analysisLabels(). */
    public function show(ExerciseAttempt $attempt): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($attempt->status === AttemptStatus::COMPLETED) {
            return redirect()->route('exercises.result', $attempt);
        }

        return view('exercises.attempt', [
            'attempt' => $attempt,
            'exercise' => $attempt->exercise,
            'questions' => $this->service->questionsFor($attempt),
        ]);
    }

    public function submit(SubmitExerciseRequest $request, ExerciseAttempt $attempt): RedirectResponse
    {
        Gate::authorize('view', $attempt);

        $this->service->submit($attempt, $request->validated('answers') ?? []);

        return redirect()->route('exercises.result', $attempt);
    }

    public function result(ExerciseAttempt $attempt, AnswerEvaluationService $evaluator): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($attempt->status !== AttemptStatus::COMPLETED) {
            return redirect()->route('exercises.attempt', $attempt);
        }

        $attempt->load('exercise');

        return view('exercises.result', [
            'attempt' => $attempt,
            'exercise' => $attempt->exercise,
            'answers' => $this->service->answersFor($attempt),
            'evaluator' => $evaluator, // untuk analysisBreakdown() di view
        ]);
    }
}
