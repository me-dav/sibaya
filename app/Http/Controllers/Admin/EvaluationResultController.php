<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationAttempt;
use App\Models\User;
use App\Services\EvaluationBuilderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** Admin hanya MELIHAT hasil evaluasi; nilai tidak dapat diubah. */
class EvaluationResultController extends Controller
{
    public function index(Request $request, Evaluation $evaluation): View
    {
        Gate::authorize('viewAny', User::class);

        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $base = EvaluationAttempt::query()->where('evaluation_id', $evaluation->id);
        $done = (clone $base)->whereIn('status', ['completed', 'expired']);

        $attempts = (clone $base)->with('user')
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($u) => $u->where(fn ($w) => $w
                ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])->orWhereRaw('LOWER(email) LIKE ?', ["%{$q}%"]))))
            ->when($request->filled('class'), fn ($query) => $query->whereHas('user', fn ($u) => $u->where('class_name', $request->query('class'))))
            ->latest('started_at')->paginate(15)->withQueryString();

        return view('admin.evaluations.results', [
            'evaluation' => $evaluation,
            'attempts'   => $attempts,
            'average'    => (clone $done)->avg('score'),
            'passRate'   => (clone $done)->count() > 0 ? round((clone $done)->where('score', '>=', $evaluation->passing_score)->count() / (clone $done)->count() * 100) : null,
            'total'      => (clone $base)->count(),
        ]);
    }

    public function show(EvaluationAttempt $evaluationAttempt, EvaluationBuilderService $builder): View
    {
        Gate::authorize('viewAny', User::class);

        $evaluationAttempt->load('user');
        $evaluation = Evaluation::findOrFail($evaluationAttempt->evaluation_id);

        return view('admin.evaluations.result-show', [
            'attempt'    => $evaluationAttempt,
            'evaluation' => $evaluation,
            'questions'  => $builder->questionsOf($evaluation->id),
            'answers'    => EvaluationAnswer::where('evaluation_attempt_id', $evaluationAttempt->id)->get()->keyBy('question_id'),
        ]);
    }
}
