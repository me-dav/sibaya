<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class EvaluationFlowTest extends TestCase
{
    use MakesEvaluations, MakesMaterials, RefreshDatabase;

    public function test_halaman_evaluasi_publik_menampilkan_uts_dan_uas(): void
    {
        $this->makeMc($uts = $this->makeEvaluation('uts', ['title' => 'UTS Ganjil']));
        $this->makeShort($this->makeEvaluation('uas', ['title' => 'UAS Ganjil']));
        $this->makeEvaluation('uts', ['title' => 'UTS Draft', 'is_published' => false]);

        $this->get(route('evaluations.index'))->assertOk()->assertSee('UTS Ganjil')->assertSee('UAS Ganjil')->assertDontSee('UTS Draft');
    }

    public function test_tamu_tidak_dapat_memulai(): void
    {
        $e = $this->makeEvaluation();
        $this->makeMc($e);

        $this->post(route('evaluations.start', $e))->assertRedirect(route('login'));
    }

    public function test_siswa_memulai_dan_attempt_tersimpan_dan_resume_tidak_menggandakan(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        $this->makeMc($e);

        $this->actingAs($s)->post(route('evaluations.start', $e))->assertRedirect();
        $this->actingAs($s)->post(route('evaluations.start', $e))->assertRedirect();

        $this->assertSame(1, DB::table('evaluation_attempts')->where('user_id', $s->id)->count());
    }

    public function test_admin_tidak_dapat_mengerjakan_evaluasi(): void
    {
        $e = $this->makeEvaluation();
        $this->makeMc($e);

        $this->actingAs($this->admin())->post(route('evaluations.start', $e))->assertForbidden();
    }

    public function test_evaluasi_draft_atau_tanpa_soal_atau_di_luar_jadwal_ditolak(): void
    {
        $s = $this->siswa();

        $draft = $this->makeEvaluation('uts', ['is_published' => false]);
        $this->actingAs($s)->post(route('evaluations.start', $draft))->assertForbidden();

        $empty = $this->makeEvaluation();
        $this->actingAs($s)->post(route('evaluations.start', $empty))->assertSessionHas('error');

        $future = $this->makeEvaluation('uts', ['start_at' => now()->addDay()]);
        $this->makeMc($future);
        $this->actingAs($s)->post(route('evaluations.start', $future))->assertSessionHas('error');

        $closed = $this->makeEvaluation('uts', ['end_at' => now()->subDay()]);
        $this->makeMc($closed);
        $this->actingAs($s)->post(route('evaluations.start', $closed))->assertSessionHas('error');

        $this->assertSame(0, DB::table('evaluation_attempts')->count());
    }

    public function test_batas_percobaan_berlaku(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['max_attempts' => 1]);
        $this->makeMc($e);

        $this->actingAs($s)->post(route('evaluations.start', $e));
        $a = DB::table('evaluation_attempts')->first();
        $this->actingAs($s)->post(route('evaluations.submit', $a->id));

        $this->actingAs($s)->post(route('evaluations.start', $e))->assertSessionHas('error');
        $this->assertSame(1, DB::table('evaluation_attempts')->count());
    }

    public function test_uts_pilihan_ganda_dinilai_di_server_dan_tersimpan(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts');
        [$q1, $correct1] = $this->makeMc($e, 2);
        [$q2, , $wrong2] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);

        // Klien mencoba mengirim "score" sendiri: harus diabaikan.
        $this->actingAs($s)->post(route('evaluations.submit', $a), [
            'score' => 100, 'answers' => [$q1->id => $correct1->id, $q2->id => $wrong2->id],
        ])->assertRedirect(route('evaluations.attempts.result', $a));

        $a->refresh();
        $this->assertSame('completed', $a->status);
        $this->assertEquals(50, (float) $a->score);
        $this->assertSame(1, (int) $a->correct_answers);
        $this->assertSame(1, (int) $a->wrong_answers);
        $this->assertEquals(4, (float) $a->total_points);
        $this->assertNotNull($a->submitted_at);
    }

    public function test_uas_isian_dinilai_dengan_kata_kunci_dan_kosong_nol(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uas');
        $q1 = $this->makeShort($e, 5);
        $q2 = $this->makeShort($e, 5);
        $a = $this->makeEvalAttempt($s, $e);

        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q1->id => 'Ini adalah Makna Kias dari ungkapan', $q2->id => '']]);

        $a->refresh();
        $this->assertEquals(50, (float) $a->score);
        $this->assertDatabaseHas('evaluation_answers', ['evaluation_attempt_id' => $a->id, 'question_id' => $q2->id, 'is_correct' => false, 'points_earned' => 0]);
    }

    public function test_autosave_lalu_submit_tanpa_kirim_ulang_jawaban(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);

        $this->actingAs($s)->putJson(route('evaluations.attempts.answers', $a), ['answers' => [$q->id => (string) $correct->id]])
            ->assertOk()->assertJson(['saved' => true]);

        $this->actingAs($s)->post(route('evaluations.submit', $a));

        $this->assertEquals(100, (float) $a->fresh()->score);
    }

    public function test_opsi_milik_soal_lain_tidak_diterima(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q1] = $this->makeMc($e);
        [, $otherCorrect] = $this->makeMc($e);
        $a = $this->makeEvalAttempt($s, $e);

        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q1->id => $otherCorrect->id]]);

        $this->assertEquals(0, (float) $a->fresh()->score);
    }

    public function test_timer_divalidasi_server_jawaban_setelah_waktu_habis_ditolak(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['duration_minutes' => 10]);
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e, ['started_at' => now()]);

        $this->travel(11)->minutes();

        $this->actingAs($s)->putJson(route('evaluations.attempts.answers', $a), ['answers' => [$q->id => (string) $correct->id]])
            ->assertStatus(409);

        $a->refresh();
        $this->assertSame('expired', $a->status);
        $this->assertEquals(0, (float) $a->score);

        // submit setelah expired: tidak bisa mengubah hasil
        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q->id => $correct->id]]);
        $this->assertEquals(0, (float) $a->fresh()->score);
        $this->assertSame('expired', $a->fresh()->status);
    }

    public function test_submit_setelah_waktu_habis_menilai_jawaban_yang_sudah_tersimpan(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['duration_minutes' => 10]);
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);

        $this->actingAs($s)->putJson(route('evaluations.attempts.answers', $a), ['answers' => [$q->id => (string) $correct->id]])->assertOk();

        $this->travel(15)->minutes();
        $this->actingAs($s)->get(route('evaluations.attempts.show', $a))->assertRedirect(route('evaluations.attempts.result', $a));

        $a->refresh();
        $this->assertSame('expired', $a->status);
        $this->assertEquals(100, (float) $a->score);
    }

    public function test_submit_ganda_idempotent(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);

        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q->id => $correct->id]]);
        $first = $a->fresh()->submitted_at;

        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q->id => $correct->id + 999]]);

        $this->assertEquals(100, (float) $a->fresh()->score);
        $this->assertEquals($first, $a->fresh()->submitted_at);
    }

    public function test_siswa_tidak_dapat_mengakses_attempt_milik_siswa_lain(): void
    {
        $owner = $this->siswa();
        $other = $this->siswa();
        $e = $this->makeEvaluation();
        $this->makeMc($e);
        $a = $this->makeEvalAttempt($owner, $e);

        $this->actingAs($other)->get(route('evaluations.attempts.show', $a))->assertNotFound();
        $this->actingAs($other)->get(route('evaluations.attempts.result', $a))->assertNotFound();
        $this->actingAs($other)->post(route('evaluations.submit', $a))->assertNotFound();
        $this->actingAs($other)->putJson(route('evaluations.attempts.answers', $a), ['answers' => []])->assertNotFound();
    }

    public function test_form_pengerjaan_tidak_membocorkan_kunci_jawaban(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q] = $this->makeMc($e);
        $a = $this->makeEvalAttempt($s, $e);

        $html = $this->actingAs($s)->get(route('evaluations.attempts.show', $a))->assertOk()->assertSee('Opsi A')->getContent();

        $this->assertStringNotContainsString('is_correct', $html);
        $this->assertStringNotContainsString('answer_key', $html);
    }

    public function test_riwayat_dan_status_lulus_tampil(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['passing_score' => 75]);
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);
        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q->id => $correct->id]]);

        $this->get(route('evaluations.attempts.result', $a))->assertOk()->assertSee('Lulus')->assertSee('100,0');
        $this->get(route('evaluations.index'))->assertOk()->assertSee($e->title)->assertSee('100,0');
    }

    public function test_command_expire_menutup_attempt_kedaluwarsa(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['duration_minutes' => 5]);
        $this->makeMc($e);
        $a = $this->makeEvalAttempt($s, $e);

        $this->travel(10)->minutes();
        $this->artisan('evaluations:expire')->assertSuccessful();

        $this->assertSame('expired', $a->fresh()->status);
    }
}
