<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use App\Models\Question;
use App\Models\RinenggaType;
use App\Services\QuestionService;
use App\Support\Labels;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExerciseController extends Controller
{
    public function __construct(private readonly QuestionService $questions)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('create', Exercise::class);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $exercises = Exercise::query()
            ->withCount('questions')
            ->when($q !== '', fn ($query) => $query->whereRaw('LOWER(title) LIKE ?', ["%{$q}%"]))
            ->when(array_key_exists((string) $request->query('type'), Labels::exerciseTypes()),
                fn ($query) => $query->where('exercise_type', $request->query('type')))
            ->when(in_array($request->query('status'), ['published', 'draft'], true),
                fn ($query) => $query->where('is_published', $request->query('status') === 'published'))
            ->latest('updated_at')->paginate(10)->withQueryString();

        return view('admin.exercises.index', compact('exercises'));
    }

    public function create(): View
    {
        Gate::authorize('create', Exercise::class);

        return view('admin.exercises.create', [
            'exercise' => new Exercise(),
            'types'    => RinenggaType::orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreExerciseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_published'] = false; // latihan baru selalu draft sampai ada soal

        $exercise = new Exercise();
        $exercise->forceFill($data)->save();

        return redirect()->route('admin.exercises.show', $exercise)
            ->with('status', 'Latihan kasil digawe. Saiki tambahna soal.');
    }

    /** Halaman "Kelola Soal". */
    public function show(Exercise $exercise): View
    {
        Gate::authorize('update', $exercise);

        $questions = Question::query()
            ->where('exercise_id', $exercise->id)
            ->with(['options' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.exercises.show', [
            'exercise'     => $exercise,
            'questions'    => $questions,
            'allowedTypes' => Labels::allowedQuestionTypes(Labels::value($exercise->exercise_type)),
            'usedIds'      => $this->questions->usedQuestionIds($questions->pluck('id')),
        ]);
    }

    public function edit(Exercise $exercise): View
    {
        Gate::authorize('update', $exercise);

        return view('admin.exercises.edit', [
            'exercise' => $exercise,
            'types'    => RinenggaType::orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateExerciseRequest $request, Exercise $exercise): RedirectResponse
    {
        $exercise->forceFill($request->validated())->save();

        return redirect()->route('admin.exercises.show', $exercise)->with('status', 'Latihan kasil dianyari.');
    }

    public function destroy(Exercise $exercise): RedirectResponse
    {
        Gate::authorize('delete', $exercise);

        if (! $this->questions->exerciseCanBeDeleted($exercise)) {
            return back()->with('error', 'Latihan iki wis duwe riwayat siswa utawa soale wis dipakai, ora bisa dibusak. Ubah dadi Draft wae.');
        }

        $this->questions->deleteExercise($exercise);

        return redirect()->route('admin.exercises.index')->with('status', 'Latihan kasil dibusak.');
    }
}
