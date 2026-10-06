<?php

use App\Http\Controllers\ExerciseAttemptController;
use App\Http\Controllers\ExerciseAudioController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// SEMENTARA: Beranda final dibuat di Tahap 3 setelah desain dikonfirmasi.
Route::view('/', 'home.placeholder')->name('home');

// Satu pintu masuk setelah login: admin -> /admin, siswa -> dashboard siswa.
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

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
    // Audio privat: streaming terotorisasi, tanpa URL publik langsung
    Route::get('/gladhen-mandiri/percobaan/{attempt}/audio/{question}', [ExerciseAudioController::class, 'show'])->name('exercises.audio');
    Route::post('/gladhen-mandiri/{exercise}/start', [ExerciseAttemptController::class, 'start'])->name('exercises.start');
});

Route::get('/gladhen-mandiri/{exercise}', [ExerciseController::class, 'show'])->name('exercises.show');

// Area admin. Seluruh route admin wajib lewat middleware role:admin.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // SEMENTARA: dashboard admin final menunggu desain.
        Route::view('/', 'admin.placeholder')->name('dashboard');
    });

require __DIR__.'/auth.php';
