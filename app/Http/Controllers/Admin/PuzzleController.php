<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePuzzleRequest;
use App\Http\Requests\UpdatePuzzleRequest;
use App\Models\Puzzle;
use App\Models\PuzzleAttempt;
use App\Services\PuzzleService;
use App\Support\PuzzleRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PuzzleController extends Controller
{
    public function __construct(private readonly PuzzleService $service)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('create', Puzzle::class);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $puzzles = Puzzle::query()
            ->select('puzzles.*')
            ->addSelect(['attempts_count' => DB::table('puzzle_attempts')->selectRaw('count(*)')->whereColumn('puzzle_id', 'puzzles.id')->whereNotNull('completed_at')])
            ->when($q !== '', fn ($query) => $query->whereRaw('LOWER(title) LIKE ?', ["%{$q}%"]))
            ->when(array_key_exists((string) $request->query('type'), PuzzleRules::types()), fn ($query) => $query->where('puzzle_type', $request->query('type')))
            ->when(in_array($request->query('status'), ['published', 'draft'], true), fn ($query) => $query->where('is_published', $request->query('status') === 'published'))
            ->latest('updated_at')->paginate(10)->withQueryString();

        return view('admin.puzzles.index', compact('puzzles'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Puzzle::class);

        $type = array_key_exists((string) $request->query('type'), PuzzleRules::types()) ? $request->query('type') : 'susun_kata';

        return view('admin.puzzles.create', ['puzzle' => new Puzzle(), 'type' => $type]);
    }

    public function store(StorePuzzleRequest $request): RedirectResponse
    {
        $this->service->create($request->validated() + ['is_published' => $request->boolean('is_published')]);

        return redirect()->route('admin.puzzles.index')->with('status', 'Puzzle kasil disimpen.');
    }

    public function edit(Puzzle $puzzle): View
    {
        Gate::authorize('update', $puzzle);

        return view('admin.puzzles.edit', ['puzzle' => $puzzle, 'type' => \App\Support\Labels::value($puzzle->puzzle_type)]);
    }

    public function update(UpdatePuzzleRequest $request, Puzzle $puzzle): RedirectResponse
    {
        $this->service->update($puzzle, $request->validated() + ['is_published' => $request->boolean('is_published')]);

        return redirect()->route('admin.puzzles.index')->with('status', 'Puzzle kasil dianyari.');
    }

    public function destroy(Puzzle $puzzle): RedirectResponse
    {
        Gate::authorize('delete', $puzzle);

        if (! $this->service->canDelete($puzzle)) {
            return back()->with('error', 'Puzzle iki wis duwe riwayat percobaan siswa, ora bisa dibusak. Ubah dadi Draft wae.');
        }

        $puzzle->delete();

        return redirect()->route('admin.puzzles.index')->with('status', 'Puzzle kasil dibusak.');
    }

    /** Riwayat percobaan siswa pada satu puzzle. */
    public function attempts(Request $request, Puzzle $puzzle): View
    {
        Gate::authorize('update', $puzzle);

        $base = PuzzleAttempt::where('puzzle_id', $puzzle->id)->whereNotNull('completed_at');
        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $attempts = (clone $base)->with('user')
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])))
            ->latest('completed_at')->paginate(15)->withQueryString();

        return view('admin.puzzles.attempts', [
            'puzzle' => $puzzle, 'attempts' => $attempts,
            'total' => (clone $base)->count(), 'average' => (clone $base)->avg('score'), 'best' => (clone $base)->max('score'),
        ]);
    }
}
