<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachEvaluationQuestionsRequest;
use App\Http\Requests\StoreEvaluationQuestionRequest;
use App\Http\Requests\UpdateEvaluationQuestionRequest;
use App\Models\Evaluation;
use App\Models\Question;
use App\Models\RinenggaType;
use App\Services\EvaluationBuilderService;
use App\Services\QuestionService;
use App\Support\EvaluationRules;
use App\Support\Labels;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EvaluationQuestionController extends Controller
{
    private const LOCKED = 'Evaluasi iki wis duwe percobaan siswa, daftar soal ora bisa diowahi maneh.';

    public function __construct(
        private readonly EvaluationBuilderService $builder,
        private readonly QuestionService $questions,
    ) {
    }

    private function viewData(Evaluation $evaluation): array
    {
        return [
            'evaluation'    => $evaluation,
            'rinenggaTypes' => RinenggaType::orderBy('sort_order')->get(),
            'cancelUrl'     => route('admin.evaluations.show', $evaluation),
        ];
    }

    public function create(Evaluation $evaluation): View
    {
        Gate::authorize('update', $evaluation);

        $type = EvaluationRules::questionType((string) Labels::value($evaluation->type));

        return view('admin.evaluations.question-form', $this->viewData($evaluation) + [
            'title' => 'Tambah Soal Evaluasi', 'question' => new Question(), 'allowedTypes' => [$type], 'type' => $type,
            'action' => route('admin.evaluations.questions.store', $evaluation), 'method' => 'POST', 'isUsed' => false,
        ]);
    }

    public function store(StoreEvaluationQuestionRequest $request, Evaluation $evaluation): RedirectResponse
    {
        if ($this->builder->hasAttempts($evaluation)) {
            return back()->with('error', self::LOCKED);
        }

        $question = $this->questions->create(null, $request->validated(), $request->file('audio_file'));
        $this->builder->attach($evaluation, [$question->id]);

        return redirect()->route('admin.evaluations.show', $evaluation)->with('status', 'Soal kasil ditambahake.');
    }

    public function edit(Evaluation $evaluation, Question $question): View
    {
        Gate::authorize('update', $evaluation);
        $this->ensureStandalone($evaluation, $question);

        return view('admin.evaluations.question-form', $this->viewData($evaluation) + [
            'title' => 'Ubah Soal Evaluasi', 'question' => $question->load(['options' => fn ($q) => $q->orderBy('sort_order')]),
            'type' => Labels::value($question->question_type), 'allowedTypes' => [],
            'action' => route('admin.evaluations.questions.update', [$evaluation, $question]), 'method' => 'PUT',
            'isUsed' => $this->questions->isUsed($question),
        ]);
    }

    public function update(UpdateEvaluationQuestionRequest $request, Evaluation $evaluation, Question $question): RedirectResponse
    {
        $this->ensureStandalone($evaluation, $question);

        $this->questions->update($question, $request->validated(), $request->file('audio_file'));

        return redirect()->route('admin.evaluations.show', $evaluation)->with('status', 'Soal kasil dianyari.');
    }

    public function attach(AttachEvaluationQuestionsRequest $request, Evaluation $evaluation): RedirectResponse
    {
        if ($this->builder->hasAttempts($evaluation)) {
            return back()->with('error', self::LOCKED);
        }

        $added = $this->builder->attach($evaluation, $request->validated('question_ids'));

        return redirect()->route('admin.evaluations.show', $evaluation)->with('status', "{$added} soal kasil ditambahake.");
    }

    public function detach(Evaluation $evaluation, Question $question): RedirectResponse
    {
        Gate::authorize('update', $evaluation);
        abort_unless($this->builder->isAttached($evaluation, $question), 404);

        if ($this->builder->hasAttempts($evaluation)) {
            return back()->with('error', self::LOCKED);
        }

        $this->builder->detach($evaluation, $question);

        return back()->with('status', 'Soal dicopot saka evaluasi (isih ana ing bank soal).');
    }

    public function move(Request $request, Evaluation $evaluation, Question $question): RedirectResponse
    {
        Gate::authorize('update', $evaluation);
        abort_unless($this->builder->isAttached($evaluation, $question), 404);

        if ($this->builder->hasAttempts($evaluation)) {
            return back()->with('error', self::LOCKED);
        }

        $this->builder->move($evaluation, $question, $request->validate(['direction' => ['required', 'in:up,down']])['direction']);

        return back();
    }

    /** Hanya soal mandiri milik evaluasi ini yang diedit di sini; soal latihan diedit lewat latihannya. */
    private function ensureStandalone(Evaluation $evaluation, Question $question): void
    {
        abort_unless($question->exercise_id === null && $this->builder->isAttached($evaluation, $question), 404);
    }
}
