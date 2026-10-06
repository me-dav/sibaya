<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExerciseViewsTest extends TestCase
{
    use RefreshDatabase;

    /** Latihan dengan keempat tipe soal. */
    private function fullExercise(): array
    {
        Storage::fake('local');
        $svc = app(QuestionService::class);
        $exercise = Exercise::factory()->create(['title' => 'Latihan Lengkap', 'slug' => 'lengkap']);

        $mc = $svc->create(['question_type' => 'multiple_choice', 'exercise_id' => $exercise->id, 'question_text' => 'Apa iku paribasan?',
            'option_a' => 'Pilihan satu', 'option_b' => 'Pilihan loro', 'correct_option' => 'A', 'points' => 1]);
        $audio = $svc->create(['question_type' => 'audio_choice', 'exercise_id' => $exercise->id, 'question_text' => 'Apa sing krungu?',
            'option_a' => 'Audio A', 'option_b' => 'Audio B', 'correct_option' => 'B', 'points' => 1],
            UploadedFile::fake()->create('r.mp3', 20, 'audio/mpeg'));
        $short = $svc->create(['question_type' => 'short_answer', 'exercise_id' => $exercise->id, 'question_text' => 'Jelasna saloka',
            'answer_key' => 'RAHASIA-KUNCI-ISIAN', 'keywords' => 'kata-kunci-rahasia', 'points' => 1]);
        $analysis = $svc->create(['question_type' => 'text_analysis', 'exercise_id' => $exercise->id, 'passage_text' => 'Kebo nusu gudel mlaku',
            'analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => 'kebo nusu gudel', 'points' => 2],
                ['label' => 'Bebasan', 'keywords' => 'KUNCI-BARIS-RAHASIA', 'points' => 1],
            ]]);

        return [$exercise, compact('mc', 'audio', 'short', 'analysis')];
    }

    private function startAs(User $u, Exercise $e): ExerciseAttempt
    {
        $this->actingAs($u)->post(route('exercises.start', $e));

        return ExerciseAttempt::where('user_id', $u->id)->latest('id')->firstOrFail();
    }

    // ---------- Daftar ----------

    public function test_index_is_public_and_lists_only_published(): void
    {
        $pub = Exercise::factory()->create(['title' => 'Terbit Satu']);
        Question::factory()->withOptions()->create(['exercise_id' => $pub->id]);
        Exercise::factory()->create(['title' => 'Draft Rahasia', 'is_published' => false]);

        $this->get('/gladhen-mandiri')
            ->assertOk()
            ->assertSee('Basa Rinengga')
            ->assertSee('Terbit Satu')
            ->assertDontSee('Draft Rahasia')
            ->assertSee('Mlebu supaya progres');
    }

    public function test_index_filters_by_type_and_ignores_invalid_type(): void
    {
        Exercise::factory()->create(['title' => 'Gladhen Nyimak X', 'exercise_type' => 'nyimak']);
        Exercise::factory()->create(['title' => 'Gladhen Campur Y', 'exercise_type' => 'campuran']);

        $this->get('/gladhen-mandiri?jenis=nyimak')->assertSee('Gladhen Nyimak X')->assertDontSee('Gladhen Campur Y');
        $this->get('/gladhen-mandiri?jenis=ngawur')->assertOk()->assertSee('Gladhen Nyimak X')->assertSee('Gladhen Campur Y');
    }

    public function test_index_shows_five_type_cards_and_empty_state(): void
    {
        $this->get('/gladhen-mandiri')
            ->assertSee('Latihan Pemahaman')->assertSee('Latihan Nyimak')->assertSee('Latihan Analisis')
            ->assertSee('Latihan Miturut Materi')->assertSee('Latihan Campuran')
            ->assertSee('Durung ana latihan');
    }

    public function test_index_shows_progress_for_logged_in_student(): void
    {
        [$exercise] = $this->fullExercise();
        $user = User::factory()->create();
        $attempt = $this->startAs($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $attempt), []);

        $this->actingAs($user)->get('/gladhen-mandiri')->assertOk()->assertSee('1/1')->assertSee('Skor Tertinggi');
    }

    // ---------- Detail ----------

    public function test_show_page_for_guest_and_student(): void
    {
        [$exercise] = $this->fullExercise();

        $this->get(route('exercises.show', $exercise))->assertOk()->assertSee('Latihan Lengkap')->assertSee('Mlebu kanggo miwiti');

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('exercises.show', $exercise))->assertOk()->assertSee('Wiwiti Latihan')->assertSee('Durung ana percobaan');
    }

    public function test_show_page_resumes_running_attempt_and_lists_history(): void
    {
        [$exercise] = $this->fullExercise();
        $user = User::factory()->create();
        $this->startAs($user, $exercise);

        $this->actingAs($user)->get(route('exercises.show', $exercise))->assertSee('Terusake Latihan')->assertSee('Durung rampung');
    }

    public function test_draft_exercise_is_404_for_student_but_visible_to_admin(): void
    {
        $draft = Exercise::factory()->create(['is_published' => false, 'slug' => 'draft-x']);

        $this->actingAs(User::factory()->create())->get(route('exercises.show', $draft))->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->get(route('exercises.show', $draft))->assertOk();
    }

    // ---------- Pengerjaan ----------

    public function test_attempt_page_renders_all_question_types_without_leaking_keys(): void
    {
        [$exercise] = $this->fullExercise();
        $user = User::factory()->create();
        $attempt = $this->startAs($user, $exercise);

        $res = $this->actingAs($user)->get(route('exercises.attempt', $attempt));

        $res->assertOk()
            ->assertSee('Apa iku paribasan?')->assertSee('Pilihan loro')
            ->assertSee('Apa sing krungu?')
            ->assertSee('Jelasna saloka')
            ->assertSee('Kebo nusu gudel mlaku')
            ->assertSee('Jenis Basa Rinengga')->assertSee('Saloka')->assertSee('Bebasan')
            ->assertSee('Kirim');

        // Audio: tanpa tombol unduh, lewat route terotorisasi (bukan /storage)
        $res->assertSee('nodownload', false)->assertSee('/audio/', false)->assertDontSee('/storage/', false);

        // Kunci jawaban tidak boleh ada di HTML
        $res->assertDontSee('RAHASIA-KUNCI-ISIAN')->assertDontSee('kata-kunci-rahasia')->assertDontSee('KUNCI-BARIS-RAHASIA')->assertDontSee('Wangsulan bener');
    }

    public function test_attempt_page_for_other_student_is_forbidden(): void
    {
        [$exercise] = $this->fullExercise();
        $attempt = $this->startAs(User::factory()->create(), $exercise);

        $this->actingAs(User::factory()->create())->get(route('exercises.attempt', $attempt))->assertForbidden();
    }

    public function test_all_questions_appear_on_randomized_attempt_page(): void
    {
        $exercise = Exercise::factory()->create(['is_randomized' => true]);
        foreach (range(1, 6) as $i) {
            Question::factory()->withOptions()->create(['exercise_id' => $exercise->id, 'question_text' => "Soal acak {$i}", 'sort_order' => $i]);
        }
        $user = User::factory()->create();
        $attempt = $this->startAs($user, $exercise);

        $res = $this->actingAs($user)->get(route('exercises.attempt', $attempt));
        foreach (range(1, 6) as $i) {
            $res->assertSee("Soal acak {$i}");
        }
    }

    // ---------- Hasil ----------

    public function test_result_page_shows_score_correct_wrong_and_review(): void
    {
        [$exercise, $q] = $this->fullExercise();
        $user = User::factory()->create();
        $attempt = $this->startAs($user, $exercise);

        $this->actingAs($user)->post(route('exercises.submit', $attempt), ['answers' => [
            $q['mc']->id => $q['mc']->options()->where('option_key', 'A')->value('id'),       // benar
            $q['audio']->id => $q['audio']->options()->where('option_key', 'A')->value('id'), // salah
            $q['short']->id => 'ngawur',                                                       // salah
            $q['analysis']->id => [0 => 'kebo nusu gudel', 1 => 'x'],                          // sebagian
        ]])->assertRedirect(route('exercises.result', $attempt));

        $res = $this->actingAs($user)->get(route('exercises.result', $attempt));

        $res->assertOk()
            ->assertSee('Nilai')->assertSee('Bener')->assertSee('Salah')->assertSee('Sebagian bener')
            ->assertSee('Wangsulan bener')            // kunci pilihan ganda muncul SETELAH submit
            ->assertSee('RAHASIA-KUNCI-ISIAN')        // contoh jawaban isian
            ->assertSee('KUNCI-BARIS-RAHASIA')        // contoh jawaban baris analisis
            ->assertSee('Baleni Latihan');
    }

    public function test_result_marks_unanswered_questions(): void
    {
        [$exercise] = $this->fullExercise();
        $user = User::factory()->create();
        $attempt = $this->startAs($user, $exercise);
        $this->actingAs($user)->post(route('exercises.submit', $attempt), []);

        $this->actingAs($user)->get(route('exercises.result', $attempt))->assertSee('Ora dijawab', false);
    }

    public function test_result_of_other_student_is_forbidden(): void
    {
        [$exercise] = $this->fullExercise();
        $owner = User::factory()->create();
        $attempt = $this->startAs($owner, $exercise);
        $this->actingAs($owner)->post(route('exercises.submit', $attempt), []);

        $this->actingAs(User::factory()->create())->get(route('exercises.result', $attempt))->assertForbidden();
    }
}
