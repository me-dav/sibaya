<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\Evaluation;
use App\Services\EvaluationBuilderService;
use App\Support\EvaluationRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EvaluationController extends Controller
{
    public function __construct(private readonly EvaluationBuilderService $builder)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('create', Evaluation::class);

        $evaluations = Evaluation::query()
            ->select('evaluations.*')
            ->addSelect([
                'questions_count' => DB::table('evaluation_questions')->selectRaw('count(*)')->whereColumn('evaluation_id', 'evaluations.id'),
                'attempts_count'  => DB::table('evaluation_attempts')->selectRaw('count(*)')->whereColumn('evaluation_id', 'evaluations.id'),
            ])
            ->when(array_key_exists((string) $request->query('type'), EvaluationRules::types()), fn ($q) => $q->where('type', $request->query('type')))
            ->latest('updated_at')->paginate(10)->withQueryString();

        return view('admin.evaluations.index', compact('evaluations'));
    }

    public function create(): View
    {
        Gate::authorize('create', Evaluation::class);

        return view('admin.evaluations.create', ['evaluation' => new Evaluation(['duration_minutes' => 60, 'passing_score' => 75, 'max_attempts' => 1])]);
    }

    public function store(StoreEvaluationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_published'] = false; // diterbitkan setelah soal ditambahkan

        $evaluation = new Evaluation();
        $evaluation->forceFill($data)->save();

        return redirect()->route('admin.evaluations.show', $evaluation)->with('status', 'Evaluasi kasil digawe. Saiki tambahna soal.');
    }

    /** Halaman "Kelola Soal Evaluasi". */
    public function show(Request $request, Evaluation $evaluation): View
    {
        Gate::authorize('update', $evaluation);

        $questions = $this->builder->questionsOf($evaluation->id);

        return view('admin.evaluations.show', [
            'evaluation'    => $evaluation,
            'questions'     => $questions,
            'exerciseTitle' => DB::table('exercises')->whereIn('id', $questions->pluck('exercise_id')->filter())->pluck('title', 'id'),
            'bank'          => $this->builder->eligibleQuestions($evaluation, $request->query('bank_q')),
            'locked'        => $this->builder->hasAttempts($evaluation),
            'attempts'      => DB::table('evaluation_attempts')->where('evaluation_id', $evaluation->id)->count(),
            'publishErrors' => $this->builder->publishErrors($evaluation),
        ]);
    }

    public function edit(Evaluation $evaluation): View
    {
        Gate::authorize('update', $evaluation);

        return view('admin.evaluations.edit', compact('evaluation'));
    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->forceFill($request->validated())->save();

        return redirect()->route('admin.evaluations.show', $evaluation)->with('status', 'Evaluasi kasil dianyari.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        Gate::authorize('delete', $evaluation);

        if (! $this->builder->canDelete($evaluation)) {
            return back()->with('error', 'Evaluasi iki wis duwe percobaan siswa, ora bisa dibusak. Ubah dadi Draft wae.');
        }

        $this->builder->delete($evaluation);

        return redirect()->route('admin.evaluations.index')->with('status', 'Evaluasi kasil dibusak.');
    }
}
