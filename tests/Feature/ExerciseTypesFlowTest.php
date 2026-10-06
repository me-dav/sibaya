<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Exercise;
use App\Models\ExerciseAnswer;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use App\Models\User;
use App\Services\ExerciseAttemptService;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExerciseTypesFlowTest extends TestCase
{
    use RefreshDatabase;

    private function attempt(User $user, Exercise $exercise): ExerciseAttempt
    {
        $this->actingAs($user)->post(route('exercises.start', $exercise));

        return ExerciseAttempt::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    public function test_mixed_exercise_is_scored_end_to_end(): void
    {
        $exercise = Exercise::factory()->create();
        $svc = app(QuestionService::class);

        $mc = $svc->create([
            'question_type' => 'multiple_choice', 'exercise_id' => $exercise->id, 'question_text' => 'q',
            'option_a' => 'a', 'option_b' => 'b', 'correct_option' => 'A', 'points' => 2,
        ]);
        $short = $svc->create([
            'question_type' => 'short_answer', 'exercise_id' => $exercise->id, 'question_text' => 'q2',
            'answer_key' => 'makna kias', 'keywords' => 'arti kiasan', 'points' => 3,
        ]);
        $analysis = $svc->create([
            'question_type' => 'text_analysis', 'exercise_id' => $exercise->id, 'passage_text' => 'teks',
            'analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => 'alpha', 'points' => 3],
                ['label' => 'Bebasan', 'keywords' => 'beta', 'points' => 2],
            ],
        ]);

        $user = User::factory()->create();
        $attempt = $this->attempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), ['answers' => [
            $mc->id => $mc->options()->where('option_key', 'A')->value('id'),   // benar: 2
            $short->id => 'Itu ARTI   kiasan',                                  // benar: 3
            $analysis->id => [0 => 'Alpha', 1 => 'salah'],                      // sebagian: 3 dari 5
        ]])->assertRedirect(route('exercises.result', $attempt));

        $attempt->refresh();
        $this->assertSame(10, $attempt->total_points);       // 2 + 3 + 5
        $this->assertSame('80.00', $attempt->score);         // 8 / 10
        $this->assertSame(2, $attempt->correct_answers);     // analisis parsial = belum benar penuh
        $this->assertSame(1, $attempt->wrong_answers);
        $this->assertSame(3, ExerciseAnswer::count());

        $row = ExerciseAnswer::where('question_id', $analysis->id)->first();
        $this->assertSame(['Alpha', 'salah'], json_decode($row->answer_text, true));
        $this->assertEquals(3, $row->points_earned);
    }

    public function test_blank_short_and_analysis_answers_are_saved_with_zero(): void
    {
        $exercise = Exercise::factory()->create();
        $svc = app(QuestionService::class);
        $svc->create(['question_type' => 'short_answer', 'exercise_id' => $exercise->id, 'question_text' => 'q', 'answer_key' => 'k', 'points' => 1]);
        $svc->create(['question_type' => 'text_analysis', 'exercise_id' => $exercise->id, 'passage_text' => 't',
            'analysis_rows' => [['label' => 'X', 'keywords' => 'k', 'points' => 2]]]);

        $user = User::factory()->create();
        $attempt = $this->attempt($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $attempt), []);

        $attempt->refresh();
        $this->assertSame('0.00', $attempt->score);
        $this->assertSame(3, $attempt->total_points);
        $this->assertSame(2, ExerciseAnswer::count());
    }

    public function test_too_long_answer_is_rejected(): void
    {
        $exercise = Exercise::factory()->create();
        $q = app(QuestionService::class)->create(['question_type' => 'short_answer', 'exercise_id' => $exercise->id, 'question_text' => 'q', 'answer_key' => 'k', 'points' => 1]);
        $user = User::factory()->create();
        $attempt = $this->attempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), ['answers' => [$q->id => str_repeat('a', 501)]])
            ->assertSessionHasErrors('answers.'.$q->id);

        $this->assertSame('in_progress', $attempt->fresh()->status->value);
    }

    // ---- audio privat ----

    private function audioSetup(): array
    {
        Storage::fake('local');
        $exercise = Exercise::factory()->create();
        $q = app(QuestionService::class)->create([
            'question_type' => 'audio_choice', 'exercise_id' => $exercise->id, 'question_text' => 'dengar',
            'option_a' => 'a', 'option_b' => 'b', 'correct_option' => 'A', 'points' => 1,
        ], UploadedFile::fake()->create('r.mp3', 20, 'audio/mpeg'));

        $owner = User::factory()->create();

        return [$exercise, $q, $owner, $this->attempt($owner, $exercise)];
    }

    public function test_owner_can_stream_audio_without_download_disposition(): void
    {
        [, $q, $owner, $attempt] = $this->audioSetup();

        $res = $this->actingAs($owner)->get(route('exercises.audio', [$attempt, $q]));

        $res->assertOk()->assertHeader('Content-Type', 'audio/mpeg');
        $this->assertStringContainsString('inline', $res->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $res->headers->get('Cache-Control'));
    }

    public function test_guest_and_other_students_cannot_stream_audio(): void
    {
        [, $q, , $attempt] = $this->audioSetup();

        $this->get(route('exercises.audio', [$attempt, $q]))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('exercises.audio', [$attempt, $q]))->assertForbidden();
    }

    public function test_audio_of_another_exercise_is_not_served(): void
    {
        [, , $owner, $attempt] = $this->audioSetup();
        $foreign = Question::factory()->create([
            'exercise_id' => Exercise::factory()->create()->id,
            'question_type' => QuestionType::AUDIO_CHOICE,
            'audio_path' => 'audio/questions/foreign.mp3',
        ]);

        $this->actingAs($owner)->get(route('exercises.audio', [$attempt, $foreign]))->assertNotFound();
    }

    public function test_audio_file_has_no_public_url(): void
    {
        [, $q] = $this->audioSetup();

        Storage::disk('public')->assertMissing($q->audio_path);
        $this->assertStringNotContainsString('/storage/', route('exercises.audio', [1, $q]));
    }

    // ---- randomisasi ----

    private function randomExercise(bool $randomized): Exercise
    {
        $exercise = Exercise::factory()->create(['is_randomized' => $randomized]);
        foreach (range(1, 12) as $i) {
            Question::factory()->withOptions()->create(['exercise_id' => $exercise->id, 'sort_order' => $i]);
        }

        return $exercise;
    }

    public function test_non_randomized_exercise_keeps_master_order(): void
    {
        $exercise = $this->randomExercise(false);
        $attempt = $this->attempt(User::factory()->create(), $exercise);

        $ids = app(ExerciseAttemptService::class)->questionsFor($attempt)->pluck('id')->all();

        $this->assertSame($exercise->questions()->pluck('id')->all(), $ids);
    }

    public function test_randomized_order_is_stable_per_attempt_and_differs_between_attempts(): void
    {
        $exercise = $this->randomExercise(true);
        $svc = app(ExerciseAttemptService::class);
        $user = User::factory()->create();

        $first = $this->attempt($user, $exercise);
        $a = $svc->questionsFor($first)->pluck('id')->all();
        $b = $svc->questionsFor($first->fresh())->pluck('id')->all();
        $this->assertSame($a, $b); // reload tidak mengubah urutan

        $this->actingAs($user)->post(route('exercises.submit', $first), []);
        $second = $this->attempt($user, $exercise);
        $c = $svc->questionsFor($second)->pluck('id')->all();

        $master = $exercise->questions()->pluck('id')->all();
        $this->assertEqualsCanonicalizing($master, $a); // soal yang sama, hanya urutan
        $this->assertNotSame($master, $a);
        $this->assertNotSame($a, $c);
        $this->assertSame($master, $exercise->questions()->pluck('id')->all()); // data master utuh
    }

    public function test_randomized_answers_are_stored_by_question_id(): void
    {
        $exercise = $this->randomExercise(true);
        $user = User::factory()->create();
        $attempt = $this->attempt($user, $exercise);
        $first = $exercise->questions()->first();

        $this->actingAs($user)->post(route('exercises.submit', $attempt), [
            'answers' => [$first->id => $first->options()->where('option_key', 'A')->value('id')],
        ]);

        $this->assertTrue((bool) ExerciseAnswer::where('question_id', $first->id)->value('is_correct'));
        $this->assertSame(12, ExerciseAnswer::count());
    }
}
