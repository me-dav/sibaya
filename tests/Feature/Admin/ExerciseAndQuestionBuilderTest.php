<?php

namespace Tests\Feature\Admin;

use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class ExerciseAndQuestionBuilderTest extends TestCase
{
    use MakesExercises, MakesMaterials, RefreshDatabase;

    private function mc(array $over = []): array
    {
        return array_merge([
            'question_type' => 'multiple_choice', 'question_text' => 'Apa tegese?', 'option_a' => 'A1', 'option_b' => 'B1',
            'option_c' => 'C1', 'option_d' => '', 'correct_option' => 'B', 'points' => 2,
        ], $over);
    }

    public function test_siswa_tidak_dapat_mengelola_latihan_dan_soal(): void
    {
        $e = $this->makeExercise();
        $this->actingAs($this->siswa())->get(route('admin.exercises.index'))->assertForbidden();
        $this->actingAs($this->siswa())->post(route('admin.exercises.questions.store', $e), $this->mc())->assertForbidden();
    }

    public function test_admin_membuat_latihan_selalu_draft_dengan_slug_otomatis(): void
    {
        $this->actingAs($this->admin())->post(route('admin.exercises.store'), [
            'title' => 'Gladhen Paribasan', 'exercise_type' => 'pemahaman', 'is_published' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('exercises', ['slug' => 'gladhen-paribasan', 'is_published' => false]);
    }

    public function test_latihan_kosong_tidak_bisa_diterbitkan(): void
    {
        $e = $this->makeExercise();
        $this->actingAs($this->admin())->put(route('admin.exercises.update', $e), [
            'title' => $e->title, 'slug' => $e->slug, 'exercise_type' => 'pemahaman', 'is_published' => 1,
        ])->assertSessionHasErrors('is_published');
    }

    public function test_soal_pilihan_ganda_tersimpan_beserta_opsi_dan_kunci(): void
    {
        $e = $this->makeExercise();
        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $e), $this->mc())->assertSessionHasNoErrors();

        $q = Question::firstOrFail();
        $this->assertSame('B', $q->answer_key);
        $this->assertSame(3, DB::table('question_options')->where('question_id', $q->id)->count());
        $this->assertDatabaseHas('question_options', ['question_id' => $q->id, 'option_key' => 'B', 'is_correct' => true]);
    }

    public function test_kunci_harus_menunjuk_opsi_yang_terisi(): void
    {
        $e = $this->makeExercise();
        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $e), $this->mc(['correct_option' => 'D']))
            ->assertSessionHasErrors('correct_option');
    }

    public function test_tipe_soal_dibatasi_menurut_jenis_latihan(): void
    {
        $nyimak = $this->makeExercise('nyimak');
        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $nyimak), $this->mc())
            ->assertSessionHasErrors('question_type');
    }

    public function test_audio_choice_menyimpan_audio_di_disk_private(): void
    {
        Storage::fake('local');
        $e = $this->makeExercise('nyimak');

        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $e), $this->mc([
            'question_type' => 'audio_choice', 'audio_file' => UploadedFile::fake()->create('a.mp3', 100, 'audio/mpeg'),
        ]))->assertSessionHasNoErrors();

        $q = Question::firstOrFail();
        Storage::disk('local')->assertExists($q->audio_path);

        $this->actingAs($this->admin())->get(route('admin.questions.audio', $q))->assertOk();
        $this->actingAs($this->siswa())->get(route('admin.questions.audio', $q))->assertForbidden();
    }

    public function test_audio_choice_wajib_audio_dan_menolak_file_salah(): void
    {
        Storage::fake('local');
        $e = $this->makeExercise('nyimak');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.exercises.questions.store', $e), $this->mc(['question_type' => 'audio_choice']))
            ->assertSessionHasErrors('audio_file');
        $this->actingAs($admin)->post(route('admin.exercises.questions.store', $e), $this->mc([
            'question_type' => 'audio_choice', 'audio_file' => UploadedFile::fake()->create('x.exe', 10),
        ]))->assertSessionHasErrors('audio_file');
    }

    public function test_analisis_teks_menyimpan_baris_dan_menjumlah_poin(): void
    {
        $e = $this->makeExercise('analisis');
        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $e), [
            'question_type' => 'text_analysis', 'passage_text' => 'Teks...',
            'analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => 'kutuk, kethek', 'points' => 5],
                ['label' => 'Bebasan', 'keywords' => 'kaya', 'points' => 3],
            ],
        ])->assertSessionHasNoErrors();

        $q = Question::firstOrFail();
        $this->assertSame(8, (int) $q->points);
        $rows = data_get(is_array($q->metadata) ? $q->metadata : json_decode($q->metadata, true), 'analysis_rows');
        $this->assertSame(['kutuk', 'kethek'], $rows[0]['keywords']);
    }

    public function test_isian_singkat_memecah_kata_kunci(): void
    {
        $e = $this->makeExercise();
        $this->actingAs($this->admin())->post(route('admin.exercises.questions.store', $e), [
            'question_type' => 'short_answer', 'question_text' => 'Jelaskan', 'answer_key' => 'makna kias',
            'keywords' => "makna kias, arti kiasan\nlambang", 'points' => 3,
        ])->assertSessionHasNoErrors();

        $kw = Question::firstOrFail()->keywords;
        $kw = is_array($kw) ? $kw : json_decode($kw, true);
        $this->assertSame(['makna kias', 'arti kiasan', 'lambang'], $kw);
    }

    public function test_tipe_soal_tidak_berubah_saat_update(): void
    {
        $e = $this->makeExercise();
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.exercises.questions.store', $e), $this->mc());
        $q = Question::firstOrFail();

        $this->actingAs($admin)->put(route('admin.questions.update', $q), $this->mc(['question_type' => 'short_answer', 'question_text' => 'Baru']))
            ->assertSessionHasNoErrors();

        $q->refresh();
        $this->assertSame('multiple_choice', $q->question_type instanceof \BackedEnum ? $q->question_type->value : $q->question_type);
        $this->assertSame('Baru', $q->question_text);
    }

    public function test_soal_yang_sudah_dijawab_tidak_dapat_dihapus(): void
    {
        $e = $this->makeExercise();
        $q = $this->makeQuestion($e);
        $attempt = $this->makeAttempt($this->siswa()->id, $e->id);
        DB::table('exercise_answers')->insert([
            'exercise_attempt_id' => $attempt, 'question_id' => $q->id, 'is_correct' => false,
            'points_earned' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->admin())->delete(route('admin.questions.destroy', $q))->assertSessionHas('error');
        $this->assertDatabaseHas('questions', ['id' => $q->id]);
    }

    public function test_urutan_soal_dapat_digeser(): void
    {
        $e = $this->makeExercise();
        $a = $this->makeQuestion($e, ['sort_order' => 1]);
        $b = $this->makeQuestion($e, ['sort_order' => 2]);

        $this->actingAs($this->admin())->patch(route('admin.questions.move', $b), ['direction' => 'up'])->assertRedirect();

        $this->assertSame(1, (int) $b->fresh()->sort_order);
        $this->assertSame(2, (int) $a->fresh()->sort_order);
    }

    public function test_latihan_dengan_attempt_tidak_dapat_dihapus(): void
    {
        $e = $this->makeExercise();
        $this->makeAttempt($this->siswa()->id, $e->id);

        $this->actingAs($this->admin())->delete(route('admin.exercises.destroy', $e))->assertSessionHas('error');
        $this->assertDatabaseHas('exercises', ['id' => $e->id]);
    }

    public function test_latihan_tanpa_riwayat_dapat_dihapus_beserta_soalnya(): void
    {
        $e = $this->makeExercise();
        $this->makeQuestion($e);

        $this->actingAs($this->admin())->delete(route('admin.exercises.destroy', $e))->assertRedirect(route('admin.exercises.index'));
        $this->assertDatabaseMissing('exercises', ['id' => $e->id]);
        $this->assertDatabaseCount('questions', 0);
    }
}
