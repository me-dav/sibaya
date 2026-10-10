<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Streaming audio dari disk privat (storage/app/private).
 * Tidak ada URL publik langsung. Hanya pemilik attempt yang boleh memutar,
 * dan hanya audio milik latihan attempt itu.
 * Catatan: browser tetap harus menerima data audio untuk memutarnya,
 * jadi ini mengurangi akses, bukan menjamin audio mustahil disalin.
 */
class ExerciseAudioController extends Controller
{
    private const MIME = ['mp3' => 'audio/mpeg', 'mpga' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'oga' => 'audio/ogg'];

    public function show(ExerciseAttempt $attempt, Question $question): BinaryFileResponse
    {
        Gate::authorize('view', $attempt);

        abort_unless(
            $question->exercise_id === $attempt->exercise_id
                && $question->question_type === QuestionType::AUDIO_CHOICE
                && $question->audio_path,
            404
        );

        $disk = Storage::disk('local');
        abort_unless($disk->exists($question->audio_path), 404);

        $ext = strtolower(pathinfo($question->audio_path, PATHINFO_EXTENSION));

        // BinaryFileResponse mendukung header Range, jadi seek di pemutar tetap jalan.
        return response()->file($disk->path($question->audio_path), [
            'Content-Type' => self::MIME[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
