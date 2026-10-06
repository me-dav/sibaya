<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\ExerciseAnswer;
use App\Models\ExerciseAttempt;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/** Hasil latihan siswa. Admin hanya MELIHAT; nilai tidak dapat diubah dari sini. */
class ResultController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $attempts = ExerciseAttempt::query()
            ->with(['user', 'exercise'])
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($u) => $u->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])->orWhereRaw('LOWER(email) LIKE ?', ["%{$q}%"]))))
            ->when($request->filled('exercise'), fn ($query) => $query->where('exercise_id', $request->integer('exercise')))
            ->when($request->filled('class'), fn ($query) => $query->whereHas('user', fn ($u) => $u->where('class_name', $request->query('class'))))
            ->when(in_array($request->query('status'), ['in_progress', 'completed', 'abandoned'], true),
                fn ($query) => $query->where('status', $request->query('status')))
            ->latest('started_at')->paginate(15)->withQueryString();

        return view('admin.results.index', [
            'attempts'  => $attempts,
            'exercises' => Exercise::orderBy('title')->get(['id', 'title']),
            'classes'   => DB::table('users')->whereNotNull('class_name')->distinct()->orderBy('class_name')->pluck('class_name'),
        ]);
    }

    public function showExercise(ExerciseAttempt $exerciseAttempt): View
    {
        Gate::authorize('viewAny', User::class);

        $exerciseAttempt->load(['user', 'exercise']);

        $answers = ExerciseAnswer::query()
            ->where('exercise_attempt_id', $exerciseAttempt->id)
            ->with(['question.options', 'selectedOption'])
            ->get()
            ->sortBy(fn ($a) => $a->question?->sort_order)
            ->values();

        return view('admin.results.show', ['attempt' => $exerciseAttempt, 'answers' => $answers]);
    }
}
