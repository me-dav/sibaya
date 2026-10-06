<?php

namespace Tests\Feature;

use App\Enums\AttemptStatus;
use App\Enums\QuestionType;
use App\Models\Exercise;
use App\Models\ExerciseAnswer;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseAttemptTest extends TestCase
{
    use RefreshDatabase;

    /** Latihan published; opsi A selalu benar. */
    private function exercise(array $points = [1, 1], bool $published = true): array
    {
        $exercise = Exercise::factory()->create(['is_published' => $published]);
        $questions = [];

        foreach ($points as $i => $p) {
            $questions[] = Question::factory()->withOptions('A')->create([
                'exercise_id' => $exercise->id,
                'points' => $p,
                'sort_order' => $i,
            ]);
        }

        return [$exercise, $questions];
    }

    private function optionId(Question $q, string $key): int
    {
        return $q->options()->where('option_key', $key)->value('id');
    }

    private function startAttempt(User $user, Exercise $exercise): ExerciseAttempt
    {
        $this->actingAs($user)->post(route('exercises.start', $exercise));

        return ExerciseAttempt::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    public function test_guest_cannot_start_exercise(): void
    {
        [$exercise] = $this->exercise();

        $this->post(route('exercises.start', $exercise))->assertRedirect('/login');
        $this->assertDatabaseCount('exercise_attempts', 0);
    }

    public function test_siswa_can_start_and_in_progress_attempt_is_reused(): void
    {
        [$exercise] = $this->exercise();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('exercises.start', $exercise))
            ->assertRedirect();
        $this->actingAs($user)->post(route('exercises.start', $exercise));

        $this->assertSame(1, ExerciseAttempt::count());
        $this->assertSame(AttemptStatus::IN_PROGRESS, ExerciseAttempt::first()->status);
    }

    public function test_unpublished_exercise_cannot_be_started(): void
    {
        [$exercise] = $this->exercise(published: false);

        $this->actingAs(User::factory()->create())
            ->post(route('exercises.start', $exercise))
            ->assertNotFound();
    }

    public function test_exercise_without_questions_cannot_be_started(): void
    {
        $exercise = Exercise::factory()->create();

        $this->actingAs(User::factory()->create())
            ->from('/gladhen-mandiri')
            ->post(route('exercises.start', $exercise))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('exercise_attempts', 0);
    }

    public function test_score_is_weighted_and_calculated_on_server(): void
    {
        [$exercise, [$q1, $q2]] = $this->exercise([2, 1]);
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), [
            'answers' => [
                $q1->id => $this->optionId($q1, 'A'), // benar, 2 poin
                $q2->id => $this->optionId($q2, 'B'), // salah
            ],
            // upaya memanipulasi, harus diabaikan
            'score' => 100,
            'user_id' => 999,
        ])->assertRedirect(route('exercises.result', $attempt));

        $attempt->refresh();
        $this->assertSame(AttemptStatus::COMPLETED, $attempt->status);
        $this->assertSame('66.67', $attempt->score);
        $this->assertSame(3, $attempt->total_points);
        $this->assertSame(1, $attempt->correct_answers);
        $this->assertSame(1, $attempt->wrong_answers);
        $this->assertSame($user->id, $attempt->user_id);
        $this->assertNotNull($attempt->completed_at);
        $this->assertSame(2, $attempt->answers()->count());
    }

    public function test_blank_answers_are_saved_as_wrong_with_zero_points(): void
    {
        [$exercise] = $this->exercise([1, 1]);
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), []);

        $attempt->refresh();
        $this->assertSame('0.00', $attempt->score);
        $this->assertSame(2, $attempt->wrong_answers);

        $this->assertSame(2, ExerciseAnswer::where('exercise_attempt_id', $attempt->id)
            ->whereNull('selected_option_id')->where('is_correct', false)->count());
    }

    public function test_option_from_another_question_counts_as_blank(): void
    {
        [$exercise, [$q1, $q2]] = $this->exercise();
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), [
            'answers' => [$q1->id => $this->optionId($q2, 'A')], // opsi milik soal lain
        ]);

        $answer = ExerciseAnswer::where('question_id', $q1->id)->first();
        $this->assertNull($answer->selected_option_id);
        $this->assertFalse($answer->is_correct);
    }

    public function test_resubmitting_does_not_change_finished_attempt(): void
    {
        [$exercise, [$q1, $q2]] = $this->exercise();
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), [
            'answers' => [$q1->id => $this->optionId($q1, 'A'), $q2->id => $this->optionId($q2, 'A')],
        ]);
        $this->actingAs($user)->post(route('exercises.submit', $attempt), [
            'answers' => [$q1->id => $this->optionId($q1, 'B'), $q2->id => $this->optionId($q2, 'B')],
        ])->assertRedirect(route('exercises.result', $attempt));

        $this->assertSame('100.00', $attempt->fresh()->score);
        $this->assertSame(2, ExerciseAnswer::count());
    }

    public function test_student_can_repeat_exercise_and_all_attempts_are_saved(): void
    {
        [$exercise] = $this->exercise();
        $user = User::factory()->create();

        $first = $this->startAttempt($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $first), []);

        $second = $this->startAttempt($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $second), []);

        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(2, $user->exerciseAttempts()->count());
    }

    public function test_other_student_cannot_view_or_submit_attempt(): void
    {
        [$exercise] = $this->exercise();
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $attempt = $this->startAttempt($owner, $exercise);

        $this->actingAs($intruder)->get(route('exercises.attempt', $attempt))->assertForbidden();
        $this->actingAs($intruder)->get(route('exercises.result', $attempt))->assertForbidden();
        $this->actingAs($intruder)->post(route('exercises.submit', $attempt), [])->assertForbidden();

        $this->assertSame(AttemptStatus::IN_PROGRESS, $attempt->fresh()->status);
    }

    public function test_result_is_not_available_before_submit(): void
    {
        [$exercise] = $this->exercise();
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);

        $this->actingAs($user)->get(route('exercises.result', $attempt))
            ->assertRedirect(route('exercises.attempt', $attempt));
    }

    public function test_finished_attempt_redirects_from_form_to_result(): void
    {
        [$exercise] = $this->exercise();
        $user = User::factory()->create();
        $attempt = $this->startAttempt($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $attempt), []);

        $this->actingAs($user)->get(route('exercises.attempt', $attempt))
            ->assertRedirect(route('exercises.result', $attempt));
    }

    public function test_summary_counts_completed_exercises_and_best_score(): void
    {
        [$e1, [$q]] = $this->exercise([1]);
        [$e2] = $this->exercise([1]);
        $user = User::factory()->create();

        $a = $this->startAttempt($user, $e1);
        $this->actingAs($user)->post(route('exercises.submit', $a), [
            'answers' => [$q->id => $this->optionId($q, 'A')],
        ]);

        $summary = app(\App\Services\ExerciseAttemptService::class)->summaryFor($user);

        $this->assertSame(1, $summary['completed_exercises']);
        $this->assertSame(2, $summary['total_exercises']);
        $this->assertEquals(100, $summary['best_score']);
    }
}
