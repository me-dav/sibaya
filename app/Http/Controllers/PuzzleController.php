<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\PuzzleAttempt;
use App\Services\PuzzleService;
use App\Support\Labels;
use App\Support\PuzzleRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PuzzleController extends Controller
{
    public function __construct(private readonly PuzzleService $service)
    {
    }

    public function index(Request $request): View
    {
        $type = array_key_exists((string) $request->query('type'), PuzzleRules::types()) ? $request->query('type') : null;

        $puzzles = Puzzle::query()->where('is_published', true)
            ->when($type, fn ($q) => $q->where('puzzle_type', $type))
            ->orderBy('title')->get();

        $stats = collect();
        $history = collect();
        $titles = collect();

        if ($user = $request->user()) {
            $done = PuzzleAttempt::where('user_id', $user->id)->whereNotNull('completed_at')->latest('completed_at')->get();
            $stats = $done->groupBy('puzzle_id')->map(fn ($g) => ['best' => $g->max('score'), 'count' => $g->count()]);
            $history = $done->take(10);
            $titles = Puzzle::whereIn('id', $history->pluck('puzzle_id')->unique())->pluck('title', 'id');
        }

        return view('puzzles.index', compact('puzzles', 'type', 'stats', 'history', 'titles'));
    }

    public function show(Request $request, Puzzle $puzzle): View
    {
        abort_unless(Gate::allows('view', $puzzle), 404);

        $attempts = collect();
        if ($user = $request->user()) {
            $attempts = PuzzleAttempt::where('user_id', $user->id)->where('puzzle_id', $puzzle->id)
                ->whereNotNull('completed_at')->latest('completed_at')->limit(10)->get();
        }

        $cfg = $puzzle->configuration;

        return view('puzzles.show', [
            'puzzle'   => $puzzle,
            'attempts' => $attempts,
            'limit'    => $this->service->limitSeconds($puzzle),
            'count'    => Labels::value($puzzle->puzzle_type) === 'susun_kata' ? count($cfg['items'] ?? []) : count($cfg['words'] ?? []),
        ]);
    }
}
