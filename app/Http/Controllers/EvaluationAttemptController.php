<?php

namespace App\Http\Controllers;

use App\Exceptions\EvaluationNotAvailable;
use App\Http\Requests\SubmitEvaluationRequest;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationAttempt;
use App\Services\EvaluationBuilderService;
use App\Services\EvaluationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class EvaluationAttemptController extends Controller
{
    public function __construct(
        private readonly EvaluationService $service,
        private readonly EvaluationBuilderService $builder,
    ) {
    }

    public function start(Evaluation $evaluation): RedirectResponse
    {
        Gate::authorize('start', $evaluation);

        try {
            $attempt = $this->service->start(auth()->user(), $evaluation);
        } catch (EvaluationNotAvailable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('evaluations.attempts.show', $attempt);
    }

    public function show(EvaluationAttempt $attempt): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        $attempt = $this->service->finalizeIfExpired($attempt);
        $status = $attempt->status instanceof \BackedEnum ? $attempt->status->value : (string) $attempt->status;
        if ($status !== 'in_progress') {
            return redirect()->route('evaluations.attempts.result', $attempt);
        }

        $evaluation = Evaluation::findOrFail($attempt->evaluation_id);
        $questions = $this->builder->questionsOf($evaluation->id);
        $answers = EvaluationAnswer::where('evaluation_attempt_id', $attempt->id)->get()->keyBy('question_id');

        $saved = $questions->mapWithKeys(fn ($q) => [
            $q->id => filled($answers->get($q->id)?->selected_option_id) || filled($answers->get($q->id)?->answer_text),
        ]);

        return view('evaluations.attempt', [
            'attempt'     => $attempt,
            'evaluation'  => $evaluation,
            'questions'   => $questions,
            'answers'     => $answers,
            'saved'       => $saved,
            'deadlineMs'  => $this->service->deadline($attempt, $evaluation)->getTimestampMs(),
            'serverNowMs' => now()->getTimestampMs(),
        ]);
    }

    /** Autosave. 409 jika waktu sudah habis supaya klien memuat ulang. */
    public function saveAnswers(SubmitEvaluationRequest $request, EvaluationAttempt $attempt): JsonResponse
    {
        Gate::authorize('answer', $attempt);

        try {
            $this->service->saveAnswers($attempt, $request->validated('answers', []) ?? []);
        } catch (EvaluationNotAvailable $e) {
            return response()->json(['expired' => true, 'message' => $e->getMessage()], 409);
        }

        return response()->json(['saved' => true]);
    }

    public function submit(SubmitEvaluationRequest $request, EvaluationAttempt $attempt): RedirectResponse
    {
        Gate::authorize('answer', $attempt);

        $attempt = $this->service->submit($attempt, $request->validated('answers', []) ?? []);

        return redirect()->route('evaluations.attempts.result', $attempt);
    }

    public function result(EvaluationAttempt $attempt): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        $attempt = $this->service->finalizeIfExpired($attempt);
        $status = $attempt->status instanceof \BackedEnum ? $attempt->status->value : (string) $attempt->status;
        if ($status === 'in_progress') {
            return redirect()->route('evaluations.attempts.show', $attempt);
        }

        $evaluation = Evaluation::findOrFail($attempt->evaluation_id);
        $questions = $this->builder->questionsOf($evaluation->id);
        $answers = EvaluationAnswer::where('evaluation_attempt_id', $attempt->id)->get()->keyBy('question_id');

        $recommendations = app(\App\Services\RecommendationService::class)->forAttempt($attempt);
        $certificate = app(\App\Services\CertificateService::class)->statusFor($attempt);

        return view('evaluations.result', compact('attempt', 'evaluation', 'questions', 'answers', 'recommendations', 'certificate'));
    }
}
