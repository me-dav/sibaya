<?php

namespace Tests\Feature;

use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class EvaluationAdminTest extends TestCase
{
    use MakesEvaluations, MakesMaterials, RefreshDatabase;

    private function payload(array $over = []): array
    {
        return array_merge([
            'title' => 'UTS Ganjil', 'type' => 'uts', 'duration_minutes' => 60, 'passing_score' => 75, 'max_attempts' => 2,
        ], $over);
    }

    public function test_siswa_tidak_dapat_mengelola_evaluasi(): void
    {
        $e = $this->makeEvaluation();
        $s = $this->siswa();

        $this->actingAs($s)->get(route('admin.evaluations.index'))->assertForbidden();
        $this->actingAs($s)->post(route('admin.evaluations.store'), $this->payload())->assertForbidden();
        $this->actingAs($s)->delete(route('admin.evaluations.destroy', $e))->assertForbidden();
        $this->actingAs($s)->get(route('admin.evaluations.results', $e))->assertForbidden();
    }

    public function test_admin_membuat_evaluasi_selalu_draft_dan_validasi_dasar(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.evaluations.store'), $this->payload(['is_published' => 1]))->assertRedirect();
        $this->assertDatabaseHas('evaluations', ['title' => 'UTS Ganjil', 'is_published' => false]);

        $this->actingAs($admin)->post(route('admin.evaluations.store'), $this->payload(['duration_minutes' => 0, 'passing_score' => 101, 'type' => 'uuu']))
            ->assertSessionHasErrors(['duration_minutes', 'passing_score', 'type']);

        $this->actingAs($admin)->post(route('admin.evaluations.store'), $this->payload(['start_at' => '2026-10-10T10:00', 'end_at' => '2026-10-10T09:00']))
            ->assertSessionHasErrors('end_at');
    }

    public function test_tidak_bisa_terbit_tanpa_soal_dan_bisa_setelah_ada_soal(): void
    {
        $admin = $this->admin();
        $e = $this->makeEvaluation('uts', ['is_published' => false]);
        $data = $this->payload(['is_published' => 1]);

        $this->actingAs($admin)->put(route('admin.evaluations.update', $e), $data)->assertSessionHasErrors('is_published');

        $this->makeMc($e);
        $this->actingAs($admin)->put(route('admin.evaluations.update', $e), $data)->assertSessionHasNoErrors();
        $this->assertTrue((bool) $e->fresh()->is_published);
    }

    public function test_uts_hanya_menerima_pilihan_ganda_dan_uas_hanya_isian(): void
    {
        $admin = $this->admin();
        $uts = $this->makeEvaluation('uts');
        $uas = $this->makeEvaluation('uas');
        $short = $this->makeShort($this->makeEvaluation('uas'));          // soal isian di bank
        [$mc] = $this->makeMc($this->makeEvaluation('uts'));              // soal PG di bank

        $this->actingAs($admin)->post(route('admin.evaluations.questions.attach', $uts), ['question_ids' => [$short->id]])->assertSessionHasErrors('question_ids.0');
        $this->actingAs($admin)->post(route('admin.evaluations.questions.attach', $uas), ['question_ids' => [$mc->id]])->assertSessionHasErrors('question_ids.0');

        $this->actingAs($admin)->post(route('admin.evaluations.questions.attach', $uts), ['question_ids' => [$mc->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('evaluation_questions', ['evaluation_id' => $uts->id, 'question_id' => $mc->id]);
    }

    public function test_membuat_soal_mandiri_tipe_dipaksa_menurut_jenis_evaluasi(): void
    {
        $admin = $this->admin();
        $uts = $this->makeEvaluation('uts');

        // meski dikirim short_answer, UTS tetap membuat pilihan ganda
        $this->actingAs($admin)->post(route('admin.evaluations.questions.store', $uts), [
            'question_type' => 'short_answer', 'question_text' => 'Apa?', 'option_a' => 'A1', 'option_b' => 'B1',
            'correct_option' => 'A', 'points' => 2,
        ])->assertSessionHasNoErrors();

        $q = Question::firstOrFail();
        $this->assertNull($q->exercise_id);
        $this->assertSame('multiple_choice', $q->question_type instanceof \BackedEnum ? $q->question_type->value : $q->question_type);
        $this->assertDatabaseHas('evaluation_questions', ['evaluation_id' => $uts->id, 'question_id' => $q->id]);
    }

    public function test_soal_uas_menyimpan_jenis_rinengga_untuk_rekomendasi(): void
    {
        $admin = $this->admin();
        $type = $this->makeType();
        $uas = $this->makeEvaluation('uas');

        $this->actingAs($admin)->post(route('admin.evaluations.questions.store', $uas), [
            'question_text' => 'Jelaskan', 'answer_key' => 'makna kias', 'keywords' => 'makna kias', 'points' => 5,
            'rinengga_type_id' => $type->id,
        ])->assertSessionHasNoErrors();

        $meta = Question::firstOrFail()->metadata;
        $meta = is_array($meta) ? $meta : json_decode($meta, true);
        $this->assertSame($type->id, $meta['rinengga_type_id']);
    }

    public function test_daftar_soal_dikunci_setelah_ada_attempt(): void
    {
        $admin = $this->admin();
        $e = $this->makeEvaluation();
        [$q] = $this->makeMc($e);
        [$other] = $this->makeMc($this->makeEvaluation());
        $this->makeEvalAttempt($this->siswa(), $e);

        $this->actingAs($admin)->post(route('admin.evaluations.questions.attach', $e), ['question_ids' => [$other->id]])->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.evaluations.questions.detach', [$e, $q]))->assertSessionHas('error');
        $this->assertDatabaseHas('evaluation_questions', ['evaluation_id' => $e->id, 'question_id' => $q->id]);
        $this->assertDatabaseMissing('evaluation_questions', ['evaluation_id' => $e->id, 'question_id' => $other->id]);
    }

    public function test_jenis_evaluasi_terkunci_dan_hapus_diblokir_jika_ada_attempt(): void
    {
        $admin = $this->admin();
        $e = $this->makeEvaluation('uts');
        $this->makeMc($e);

        $this->actingAs($admin)->put(route('admin.evaluations.update', $e), $this->payload(['type' => 'uas']))->assertSessionHasErrors('type');

        $this->makeEvalAttempt($this->siswa(), $e);
        $this->actingAs($admin)->delete(route('admin.evaluations.destroy', $e))->assertSessionHas('error');
        $this->assertDatabaseHas('evaluations', ['id' => $e->id]);
    }

    public function test_hapus_evaluasi_tanpa_attempt_melepas_soal_tetapi_soal_tetap_di_bank(): void
    {
        $e = $this->makeEvaluation();
        [$q] = $this->makeMc($e);

        $this->actingAs($this->admin())->delete(route('admin.evaluations.destroy', $e))->assertRedirect(route('admin.evaluations.index'));

        $this->assertDatabaseMissing('evaluations', ['id' => $e->id]);
        $this->assertSame(0, DB::table('evaluation_questions')->count());
        $this->assertDatabaseHas('questions', ['id' => $q->id]);
    }

    public function test_admin_melihat_hasil_dan_detail(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, $correct] = $this->makeMc($e, 2);
        $a = $this->makeEvalAttempt($s, $e);
        $this->actingAs($s)->post(route('evaluations.submit', $a), ['answers' => [$q->id => $correct->id]]);

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.evaluations.results', $e))->assertOk()->assertSee($s->name)->assertSee('100,0');
        $this->actingAs($admin)->get(route('admin.results.evaluations.show', $a))->assertOk()->assertSee('Opsi B')->assertSee('Bener');
    }
}
