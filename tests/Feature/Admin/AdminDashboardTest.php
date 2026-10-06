<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use MakesExercises, MakesMaterials, RefreshDatabase;

    public function test_admin_melihat_statistik_dasar(): void
    {
        $siswa = $this->siswa();
        $siswa->forceFill(['is_active' => false])->save();
        $this->siswa();
        $e = $this->makeExercise();
        $this->makeAttempt($siswa->id, $e->id, ['score' => 60]);

        $this->actingAs($this->admin())->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total siswa')
            ->assertSee('1 akun aktif')
            ->assertSee('60,0');
    }

    public function test_siswa_tidak_dapat_membuka_dashboard_admin(): void
    {
        $this->actingAs($this->siswa())->get(route('admin.dashboard'))->assertForbidden();
    }
}
