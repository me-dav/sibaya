<?php

use App\Http\Controllers\ExerciseAttemptController;
use App\Http\Controllers\ExerciseAudioController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\PuzzleAttemptController;
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\Admin\PuzzleController as AdminPuzzleController;
use App\Http\Controllers\EvaluationAttemptController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\Admin\EvaluationController as AdminEvaluationController;
use App\Http\Controllers\Admin\EvaluationQuestionController;
use App\Http\Controllers\Admin\EvaluationResultController;
use App\Http\Controllers\Admin\ExerciseController as AdminExerciseController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\StudentController;


Route::get('/', [HomeController::class, 'index'])->name('home');

// Satu pintu masuk setelah login: admin -> /admin, siswa -> dashboard siswa.
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/ruang-piwulang', [LearningController::class, 'index'])->name('learning.index');
Route::get('/ruang-piwulang/{material:slug}', [LearningController::class, 'show'])->name('learning.show');

Route::resource('materials', MaterialController::class)->except('show');

// ---- Gladhen Mandiri (publik: daftar dan detail) ----
Route::get('/gladhen-mandiri', [ExerciseController::class, 'index'])->name('exercises.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- Gladhen Mandiri (perlu login) ----
    Route::get('/gladhen-mandiri/percobaan/{attempt}', [ExerciseAttemptController::class, 'show'])->name('exercises.attempt');
    Route::post('/gladhen-mandiri/percobaan/{attempt}/submit', [ExerciseAttemptController::class, 'submit'])->name('exercises.submit');
    Route::get('/gladhen-mandiri/percobaan/{attempt}/hasil', [ExerciseAttemptController::class, 'result'])->name('exercises.result');
    Route::get('/gladhen-mandiri/percobaan/{attempt}/audio/{question}', [ExerciseAudioController::class, 'show'])->name('exercises.audio');
    Route::post('/gladhen-mandiri/{exercise}/start', [ExerciseAttemptController::class, 'start'])->name('exercises.start');
});

Route::get('/gladhen-mandiri/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');

//evaluasi
Route::get('/evaluasi', [EvaluationController::class, 'index'])->name('evaluations.index');
Route::get('/evaluasi/{evaluation}', [EvaluationController::class, 'show'])->whereNumber('evaluation')->name('evaluations.show');

Route::middleware('auth')->group(function () {
    Route::post('/evaluasi/{evaluation}/start', [EvaluationAttemptController::class, 'start'])->whereNumber('evaluation')->name('evaluations.start');
    Route::get('/evaluasi/percobaan/{attempt}', [EvaluationAttemptController::class, 'show'])->name('evaluations.attempts.show');
    Route::put('/evaluasi/percobaan/{attempt}/jawaban', [EvaluationAttemptController::class, 'saveAnswers'])->name('evaluations.attempts.answers');
    Route::post('/evaluasi/percobaan/{attempt}/submit', [EvaluationAttemptController::class, 'submit'])->name('evaluations.submit');
    Route::get('/evaluasi/percobaan/{attempt}/hasil', [EvaluationAttemptController::class, 'result'])->name('evaluations.attempts.result');
});


// Ganti route '/wahana-teka-teki' (placeholder) dengan:
Route::get('/wahana-teka-teki', [PuzzleController::class, 'index'])->name('puzzles.index');
Route::get('/wahana-teka-teki/{puzzle:slug}', [PuzzleController::class, 'show'])->name('puzzles.show');

Route::middleware('auth')->group(function () {
    Route::post('/wahana-teka-teki/{puzzle:slug}/start', [PuzzleAttemptController::class, 'start'])->name('puzzles.start');
    Route::get('/wahana-teka-teki/percobaan/{attempt}', [PuzzleAttemptController::class, 'play'])->name('puzzles.play');
    Route::post('/wahana-teka-teki/percobaan/{attempt}/submit', [PuzzleAttemptController::class, 'submit'])->name('puzzles.submit');
    Route::get('/wahana-teka-teki/percobaan/{attempt}/hasil', [PuzzleAttemptController::class, 'result'])->name('puzzles.result');
});

// Area admin. Seluruh route admin wajib lewat middleware role:admin.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // SEMENTARA: dashboard admin final menunggu desain.
        Route::view('/', 'admin.placeholder')->name('dashboard');

        Route::resource('evaluations', AdminEvaluationController::class);

        Route::get('evaluations/{evaluation}/questions/create', [EvaluationQuestionController::class, 'create'])->name('evaluations.questions.create');
        Route::post('evaluations/{evaluation}/questions', [EvaluationQuestionController::class, 'store'])->name('evaluations.questions.store');
        Route::post('evaluations/{evaluation}/questions/attach', [EvaluationQuestionController::class, 'attach'])->name('evaluations.questions.attach');
        Route::get('evaluations/{evaluation}/questions/{question}/edit', [EvaluationQuestionController::class, 'edit'])->name('evaluations.questions.edit');
        Route::put('evaluations/{evaluation}/questions/{question}', [EvaluationQuestionController::class, 'update'])->name('evaluations.questions.update');
        Route::delete('evaluations/{evaluation}/questions/{question}', [EvaluationQuestionController::class, 'detach'])->name('evaluations.questions.detach');
        Route::patch('evaluations/{evaluation}/questions/{question}/move', [EvaluationQuestionController::class, 'move'])->name('evaluations.questions.move');

        Route::get('evaluations/{evaluation}/results', [EvaluationResultController::class, 'index'])->name('evaluations.results');
        Route::get('results/evaluations/{evaluationAttempt}', [EvaluationResultController::class, 'show'])->name('results.evaluations.show');

        Route::resource('puzzles', AdminPuzzleController::class)->except('show');
        Route::get('puzzles/{puzzle}/attempts', [AdminPuzzleController::class, 'attempts'])->name('puzzles.attempts');

        Route::resource('students', StudentController::class);
        Route::patch('students/{student}/toggle', [StudentController::class, 'toggle'])->name('students.toggle');

        Route::resource('exercises', ExerciseController::class);
        Route::resource('exercises.questions', QuestionController::class)->shallow()->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('questions/{question}/move', [QuestionController::class, 'move'])->name('questions.move');
        Route::get('questions/{question}/audio', [QuestionController::class, 'audio'])->name('questions.audio');

        Route::get('results', [ResultController::class, 'index'])->name('results.index');
        Route::get('results/exercises/{exerciseAttempt}', [ResultController::class, 'showExercise'])->name('results.exercises.show');


    });

// ===== app/Models/User.php: tambahkan relasi (opsional, sesuai CLAUDE.md bagian 20) =====
// public function puzzleAttempts() { return $this->hasMany(\App\Models\PuzzleAttempt::class); }

// ===== database/seeders/DatabaseSeeder.php: tambahkan (hanya development) =====
// $this->call(PuzzleSeeder::class);

// ===== x-admin-sidebar: tambahkan link ke admin.puzzles.index =====

require __DIR__ . '/auth.php';
