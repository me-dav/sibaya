<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Services\EvaluationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class EvaluationController extends Controller
{
    public function __construct(private readonly EvaluationService $service)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        $evaluations = Evaluation::where('is_published', true)->orderBy('title')->get()->groupBy(fn ($e) => \App\Support\Labels::value($e->type));
        $stats = $this->service->questionStats();

        $attempts = collect();
        if ($user) {
            // Rapikan attempt yang waktunya sudah habis sebelum menampilkan riwayat.
            EvaluationAttempt::where('user_id', $user->id)->where('status', 'in_progress')->get()
                ->each(fn ($a) => $this->service->finalizeIfExpired($a));
            $attempts = EvaluationAttempt::where('user_id', $user->id)->latest('started_at')->get();
        }

        $info = [];
        foreach ($evaluations->flatten() as $e) {
            $mine = $attempts->where('evaluation_id', $e->id);
            $done = $mine->whereIn('status', ['completed', 'expired']);
            $info[$e->id] = [
                'questions' => (int) ($stats->get($e->id)->n ?? 0),
                'points'    => (float) ($stats->get($e->id)->pts ?? 0),
                'used'      => $mine->count(),
                'best'      => $done->isEmpty() ? null : $done->max('score'),
                'avail'     => $this->service->availability($user, $e),
            ];
        }

        $titles = Evaluation::whereIn('id', $attempts->pluck('evaluation_id')->unique())->get(['id', 'title', 'type', 'passing_score'])->keyBy('id');

        return view('evaluations.index', [
            'uts'      => $evaluations->get('uts', collect()),
            'uas'      => $evaluations->get('uas', collect()),
            'info'     => $info,
            'history'  => $attempts->take(10),
            'recommendations' => $user ? app(\App\Services\RecommendationService::class)->summary($user) : null,
            'certificates' => $user ? app(\App\Services\CertificateService::class)->availableFor($user) : [],
            'titles'   => $titles,
        ]);
    }

    public function show(Evaluation $evaluation): View
    {
        abort_unless(Gate::allows('view', $evaluation), 404);

        $user = auth()->user();
        $stats = $this->service->questionStats()->get($evaluation->id);

        $mine = collect();
        if ($user) {
            EvaluationAttempt::where('user_id', $user->id)->where('evaluation_id', $evaluation->id)->where('status', 'in_progress')->get()
                ->each(fn ($a) => $this->service->finalizeIfExpired($a));
            $mine = EvaluationAttempt::where('user_id', $user->id)->where('evaluation_id', $evaluation->id)->latest('started_at')->get();
        }

        return view('evaluations.show', [
            'evaluation' => $evaluation,
            'questions'  => (int) ($stats->n ?? 0),
            'points'     => (float) ($stats->pts ?? 0),
            'attempts'   => $mine,
            'avail'      => $this->service->availability($user, $evaluation),
        ]);
    }
}
