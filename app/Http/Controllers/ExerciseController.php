<?php

namespace App\Http\Controllers;

use App\Enums\ExerciseType;
use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Services\ExerciseAttemptService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExerciseController extends Controller
{
    public function index(Request $request, ExerciseAttemptService $attempts): View
    {
        $type = ExerciseType::tryFrom((string) $request->query('jenis'));

        $exercises = Exercise::published()
            ->with('rinenggaType')
            ->withCount('questions')
            ->when($type, fn ($q) => $q->where('exercise_type', $type->value))
            ->latest()
            ->get();

        return view('exercises.index', [
            'exercises' => $exercises,
            'activeType' => $type,
            'types' => ExerciseType::cases(),
            'summary' => $request->user() ? $attempts->summaryFor($request->user()) : null,
        ]);
    }

    public function show(Request $request, Exercise $exercise): View
    {
        abort_unless($exercise->is_published || $request->user()?->isAdmin(), 404);

        $exercise->loadCount('questions')->load('rinenggaType');

        $history = $request->user()
            ? ExerciseAttempt::where('user_id', $request->user()->id)
                ->where('exercise_id', $exercise->id)
                ->latest('started_at')
                ->get()
            : collect();

        return view('exercises.show', compact('exercise', 'history'));
    }
}
