<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPuzzleRequest;
use App\Models\Puzzle;
use App\Models\PuzzleAttempt;
use App\Services\PuzzleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PuzzleAttemptController extends Controller
{
    public function __construct(private readonly PuzzleService $service)
    {
    }

    public function start(Puzzle $puzzle): RedirectResponse
    {
        Gate::authorize('play', $puzzle);

        return redirect()->route('puzzles.play', $this->service->start(auth()->user(), $puzzle));
    }

    public function play(PuzzleAttempt $attempt): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($attempt->completed_at !== null) {
            return redirect()->route('puzzles.result', $attempt);
        }

        $puzzle = Puzzle::findOrFail($attempt->puzzle_id);
        $deadline = $this->service->deadline($attempt, $puzzle);

        return view('puzzles.play', [
            'puzzle'      => $puzzle,
            'attempt'     => $attempt,
            'play'        => $this->service->playData($puzzle, $attempt),
            'deadlineMs'  => $deadline?->getTimestampMs(),
            'serverNowMs' => now()->getTimestampMs(),
        ]);
    }

    public function submit(SubmitPuzzleRequest $request, PuzzleAttempt $attempt): RedirectResponse
    {
        Gate::authorize('answer', $attempt);

        $result = $this->service->submit($attempt, $request->validated());

        return redirect()->route('puzzles.result', $result['attempt'])
            ->with('puzzle_review', $result['review'])
            ->with('puzzle_late', $result['late']);
    }

    public function result(PuzzleAttempt $attempt): View|RedirectResponse
    {
        Gate::authorize('view', $attempt);

        if ($attempt->completed_at === null) {
            return redirect()->route('puzzles.play', $attempt);
        }

        return view('puzzles.result', [
            'attempt' => $attempt,
            'puzzle'  => Puzzle::findOrFail($attempt->puzzle_id),
            'review'  => session('puzzle_review'), // rincian hanya sekali setelah kirim (tidak disimpan di DB)
            'late'    => (bool) session('puzzle_late'),
        ]);
    }
}
