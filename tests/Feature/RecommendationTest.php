<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\Recommendation;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use MakesEvaluations, MakesExercises, MakesMaterials, RefreshDatabase;

    /** Soal pilihan ganda mandiri yang ditandai jenis Basa Rinengga. */
    private function categorisedMc(Evaluation $e, int $typeId, int $points = 2): array
    {
        [$q, $correct, $wrong] = $this->makeMc($e, $points);
        $meta = ['rinengga_type_id' => $typeId];
        $q->forceFill(['metadata' => $q->hasCast('metadata') ? $meta : json_encode($meta)])->save();

        return [$q, $correct, $wrong];
    }

    private function submit($student, Evaluation $e, array $answers)
    {
        $a = $this->makeEvalAttempt($student, $e);
        $this->actingAs($student)->post(route('evaluations.submit', $a), ['answers' => $answers]);

        return $a->fresh();
    }

    public function test_rekomendasi_mengarah_ke_jenis_dengan_nilai_terendah(): void
    {
        $saloka = $this->makeType('saloka', 'Saloka');
        $paribasan = $this->makeType('paribasan', 'Paribasan');
        $mSaloka = $this->makeMaterial($saloka);
        $mParibasan = $this->makeMaterial($paribasan);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q1, , $wrong1] = $this->categorisedMc($e, $saloka->id);
        [$q2, $correct2] = $this->categorisedMc($e, $paribasan->id);

        $a = $this->submit($s, $e, [$q1->id => $wrong1->id, $q2->id => $correct2->id]);

        $this->assertDatabaseHas('recommendations', ['user_id' => $s->id, 'material_id' => $mSaloka->id, 'source_evaluation_attempt_id' => $a->id, 'is_read' => false]);
        $this->assertDatabaseMissing('recommendations', ['material_id' => $mParibasan->id]);

        $reason = Recommendation::firstOrFail()->reason;
        $this->assertStringContainsString('Saloka', $reason);
        $this->assertStringContainsString('0,0', $reason);
    }

    public function test_jenis_dari_latihan_dihitung_lewat_exercises_rinengga_type_id(): void
    {
        $type = $this->makeType('bebasan', 'Bebasan');
        $material = $this->makeMaterial($type);
        $exercise = $this->makeExercise('pemahaman', ['rinengga_type_id' => $type->id]);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, , $wrong] = $this->makeMc($e);
        $q->forceFill(['exercise_id' => $exercise->id])->save();

        $this->submit($s, $e, [$q->id => $wrong->id]);

        $this->assertDatabaseHas('recommendations', ['user_id' => $s->id, 'material_id' => $material->id]);
    }

    public function test_nilai_seri_menghasilkan_rekomendasi_untuk_semua_jenis_terendah(): void
    {
        $a1 = $this->makeType('saloka', 'Saloka');
        $a2 = $this->makeType('sanepa', 'Sanepa');
        $this->makeMaterial($a1);
        $this->makeMaterial($a2);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q1, , $w1] = $this->categorisedMc($e, $a1->id);
        [$q2, , $w2] = $this->categorisedMc($e, $a2->id);

        $this->submit($s, $e, [$q1->id => $w1->id, $q2->id => $w2->id]);

        $this->assertSame(2, Recommendation::where('user_id', $s->id)->count());
    }

    public function test_semua_benar_tidak_menghasilkan_rekomendasi(): void
    {
        $type = $this->makeType();
        $this->makeMaterial($type);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, $correct] = $this->categorisedMc($e, $type->id);

        $this->submit($s, $e, [$q->id => $correct->id]);

        $this->assertSame(0, Recommendation::count());
        $this->assertSame('all_good', app(RecommendationService::class)->summary($s)['state']);
    }

    public function test_soal_tanpa_kategori_tidak_membuat_rekomendasi_palsu(): void
    {
        $this->makeMaterial($this->makeType());
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, , $wrong] = $this->makeMc($e);

        $this->submit($s, $e, [$q->id => $wrong->id]);

        $this->assertSame(0, Recommendation::count());
        $summary = app(RecommendationService::class)->summary($s);
        $this->assertSame('insufficient', $summary['state']);
        $this->assertStringContainsString('durung dikelompokake', $summary['message']);
    }

    public function test_tidak_ada_rekomendasi_ke_materi_yang_tidak_ada_atau_draft(): void
    {
        $noMaterial = $this->makeType('saloka', 'Saloka');
        $draft = $this->makeType('bebasan', 'Bebasan');
        $this->makeMaterial($draft, ['is_published' => false]);
        $s = $this->siswa();

        foreach ([$noMaterial, $draft] as $t) {
            $e = $this->makeEvaluation();
            [$q, , $wrong] = $this->categorisedMc($e, $t->id);
            $this->submit($s, $e, [$q->id => $wrong->id]);
        }

        $this->assertSame(0, Recommendation::count());
        $this->assertSame('no_material', app(RecommendationService::class)->summary($s)['state']);
    }

    public function test_generate_dua_kali_tidak_menggandakan(): void
    {
        $type = $this->makeType();
        $this->makeMaterial($type);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, , $wrong] = $this->categorisedMc($e, $type->id);
        $a = $this->submit($s, $e, [$q->id => $wrong->id]);

        app(RecommendationService::class)->generateForAttempt($a);
        app(RecommendationService::class)->generateForAttempt($a);

        $this->assertSame(1, Recommendation::count());
    }

    public function test_attempt_expired_juga_menghasilkan_rekomendasi(): void
    {
        $type = $this->makeType();
        $this->makeMaterial($type);
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['duration_minutes' => 5]);
        $this->categorisedMc($e, $type->id);
        $this->makeEvalAttempt($s, $e);

        $this->travel(10)->minutes();
        $this->artisan('evaluations:expire')->assertSuccessful();

        $this->assertSame(1, Recommendation::where('user_id', $s->id)->count());
    }

    public function test_halaman_hasil_dan_evaluasi_menampilkan_rekomendasi(): void
    {
        $type = $this->makeType('saloka', 'Saloka');
        $material = $this->makeMaterial($type);
        $s = $this->siswa();
        $e = $this->makeEvaluation();
        [$q, , $wrong] = $this->categorisedMc($e, $type->id);
        $a = $this->submit($s, $e, [$q->id => $wrong->id]);

        $this->actingAs($s)->get(route('evaluations.attempts.result', $a))->assertOk()->assertSee('Rekomendasi Materi')->assertSee($material->title)->assertSee('Anyar');
        $this->actingAs($s)->get(route('evaluations.index'))->assertOk()->assertSee('Rekomendasi Materi')->assertSee($material->title);
    }

    public function test_membuka_rekomendasi_menandai_dibaca_dan_membuka_materi(): void
    {
        $type = $this->makeType();
        $material = $this->makeMaterial($type);
        $s = $this->siswa();
        $rec = $this->makeRecommendation($s->id, $material->id);

        $this->actingAs($s)->get(route('recommendations.open', $rec))->assertRedirect(route('learning.show', $material));
        $this->assertTrue((bool) $rec->fresh()->is_read);
    }

    public function test_rekomendasi_siswa_lain_tidak_dapat_dibuka(): void
    {
        $material = $this->makeMaterial($this->makeType());
        $rec = $this->makeRecommendation($this->siswa()->id, $material->id);

        $this->actingAs($this->siswa())->get(route('recommendations.open', $rec))->assertNotFound();
        $this->assertFalse((bool) $rec->fresh()->is_read);
    }

    public function test_materi_yang_ditarik_dari_publikasi_tidak_dibuka(): void
    {
        $material = $this->makeMaterial($this->makeType(), ['is_published' => false]);
        $s = $this->siswa();
        $rec = $this->makeRecommendation($s->id, $material->id);

        $this->actingAs($s)->get(route('recommendations.open', $rec))->assertRedirect(route('learning.index'))->assertSessionHas('error');
    }

    private function makeRecommendation(int $userId, int $materialId): Recommendation
    {
        $r = new Recommendation();
        $r->forceFill(['user_id' => $userId, 'material_id' => $materialId, 'reason' => 'Nilai paling rendah pada materi X.', 'is_read' => false])->save();

        return $r;
    }
}
