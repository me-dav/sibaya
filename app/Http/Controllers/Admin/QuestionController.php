<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Exercise;
use App\Models\Question;
use App\Services\QuestionService;
use App\Support\Labels;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionController extends Controller
{
    public function __construct(private readonly QuestionService $questions)
    {
    }

    public function create(Request $request, Exercise $exercise): View
    {
        Gate::authorize('update', $exercise);

        $allowed = Labels::allowedQuestionTypes(Labels::value($exercise->exercise_type));
        $type = in_array($request->query('type'), $allowed, true) ? $request->query('type') : $allowed[0];

        return view('admin.questions.create', [
            'exercise'     => $exercise,
            'question'     => new Question(),
            'allowedTypes' => $allowed,
            'type'         => $type,
        ]);
    }

    public function store(StoreQuestionRequest $request, Exercise $exercise): RedirectResponse
    {
        $this->questions->create($exercise, $request->validated(), $request->file('audio_file'));

        return redirect()->route('admin.exercises.show', $exercise)->with('status', 'Soal kasil ditambahake.');
    }

    public function edit(Question $question): View
    {
        $exercise = Exercise::findOrFail($question->exercise_id);
        Gate::authorize('update', $exercise);

        return view('admin.questions.edit', [
            'exercise' => $exercise,
            'question' => $question->load(['options' => fn ($q) => $q->orderBy('sort_order')]),
            'type'     => Labels::value($question->question_type),
            'isUsed'   => $this->questions->isUsed($question),
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->questions->update($question, $request->validated(), $request->file('audio_file'));

        return redirect()->route('admin.exercises.show', $question->exercise_id)->with('status', 'Soal kasil dianyari.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $exercise = Exercise::findOrFail($question->exercise_id);
        Gate::authorize('update', $exercise);

        if ($this->questions->isUsed($question)) {
            return back()->with('error', 'Soal iki wis dijawab siswa utawa dipakai evaluasi, ora bisa dibusak.');
        }

        $this->questions->delete($question);

        return redirect()->route('admin.exercises.show', $exercise)->with('status', 'Soal kasil dibusak.');
    }

    public function move(Request $request, Question $question): RedirectResponse
    {
        Gate::authorize('update', Exercise::findOrFail($question->exercise_id));

        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];
        $this->questions->move($question, $direction);

        return back();
    }

    /** Pratinjau audio khusus admin (audio soal tidak punya URL publik). */
    public function audio(Question $question): StreamedResponse
    {
        Gate::authorize('update', Exercise::findOrFail($question->exercise_id));
        abort_if(blank($question->audio_path), 404);

        $disk = Storage::disk($this->questions->disk());
        abort_unless($disk->exists($question->audio_path), 404);

        return $disk->response($question->audio_path, null, ['Content-Disposition' => 'inline']);
    }
}
