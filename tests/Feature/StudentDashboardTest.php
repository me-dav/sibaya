<?php

namespace Tests\Feature;

use App\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use MakesEvaluations, MakesExercises, MakesMaterials, RefreshDatabase;

    public function test_tamu_diarahkan_ke_login_dan_admin_ke_dashboard_admin(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_kosong_menampilkan_profil_dan_pesan_awal(): void
    {
        $s = $this->siswa();
        $s->forceFill(['school_name' => 'SMA Negeri 1', 'class_name' => 'X-3'])->save();

        $this->actingAs($s)->get(route('dashboard'))->assertOk()
            ->assertSee($s->name)->assertSee('SMA Negeri 1')->assertSee('X-3')
            ->assertSee('Durung ana nilai')->assertSee('Durung ana hasil gladhen')
            ->assertSee('Rekomendasi bakal katon sawise kowe ngerjakake evaluasi');
    }

    public function test_dashboard_menampilkan_nilai_uts_uas_latihan_riwayat_dan_rekomendasi(): void
    {
        $s = $this->siswa();

        $uts = $this->makeEvaluation('uts', ['title' => 'UTS Ganjil', 'passing_score' => 75]);
        $uas = $this->makeEvaluation('uas', ['title' => 'UAS Ganjil', 'passing_score' => 90]);
        $this->makeEvalAttempt($s, $uts, ['status' => 'completed', 'score' => 40, 'submitted_at' => now()->subDays(2)]);
        $this->makeEvalAttempt($s, $uts, ['status' => 'completed', 'score' => 85, 'submitted_at' => now()->subDay()]);
        $this->makeEvalAttempt($s, $uas, ['status' => 'completed', 'score' => 80, 'submitted_at' => now()]);

        $ex = $this->makeExercise('pemahaman', ['title' => 'Gladhen Saloka']);
        $this->makeAttempt($s->id, $ex->id, ['score' => 66]);

        $material = $this->makeMaterial($this->makeType('saloka', 'Saloka'));
        $rec = new Recommendation();
        $rec->forceFill(['user_id' => $s->id, 'material_id' => $material->id, 'reason' => 'Nilai paling rendah pada materi Saloka (40,0).', 'is_read' => false])->save();

        $this->actingAs($s)->get(route('dashboard'))->assertOk()
            ->assertSee('85,0')                       // nilai UTS tertinggi
            ->assertSee('✓ Lulus')                    // UTS 85 >= 75
            ->assertSee('80,0')                       // nilai UAS
            ->assertSee('✗ Durung lulus')             // UAS 80 < 90
            ->assertSee('Gladhen Saloka')->assertSee('66,0')
            ->assertSee('UTS Ganjil')
            ->assertSee('Nilai paling rendah pada materi Saloka')->assertSee('1 anyar');
    }

    public function test_dashboard_tidak_menampilkan_data_siswa_lain(): void
    {
        $me = $this->siswa();
        $other = $this->siswa();
        $e = $this->makeEvaluation('uts', ['title' => 'UTS Rahasia']);
        $this->makeEvalAttempt($other, $e, ['status' => 'completed', 'score' => 99]);
        $ex = $this->makeExercise('pemahaman', ['title' => 'Latihan Orang Lain']);
        $this->makeAttempt($other->id, $ex->id);

        $this->actingAs($me)->get(route('dashboard'))->assertOk()
            ->assertDontSee('UTS Rahasia')->assertDontSee('Latihan Orang Lain')->assertDontSee('99,0');
    }

    public function test_navbar_menampilkan_link_dashboard_untuk_user_login(): void
    {
        $this->actingAs($this->siswa())->get(route('home'))->assertOk()->assertSee(route('dashboard'), false);
    }
}
