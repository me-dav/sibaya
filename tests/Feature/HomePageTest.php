<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_dapat_dibuka_publik(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sugeng Rawuh ing')
            ->assertSee('Ruang Piwulang')
            ->assertSee('Gladhen Mandiri')
            ->assertSee('Evaluasi')
            ->assertSee('Wahana Teka-teki');
    }

    public function test_tamu_melihat_tombol_login(): void
    {
        $this->get('/')->assertSee('Login')->assertDontSee('Logout');
    }

    public function test_user_login_melihat_profil_dan_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Profil')
            ->assertSee('Logout');
    }
}
