<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_is_public_and_shows_hero_cards_and_highlights(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sugeng Rawuh ing')
            ->assertSee('Sinau Basa lan Budaya Jawa')
            ->assertSee('Capaian Pembelajaran')
            ->assertSee('Elemen Menyimak')
            ->assertSee('Ruang Piwulang')
            ->assertSee('Gladhèn Mandiri')
            ->assertSee('Wahana Teka-teki')
            ->assertSee('Ndhukung Profil Pelajar Pancasila')
            ->assertSee('Basa Jawa ora mung tembung');
    }

    public function test_home_cards_link_to_real_routes(): void
    {
        $res = $this->get('/');

        foreach (['learning.index', 'exercises.index', 'puzzles.index'] as $name) {
            $res->assertSee('href="'.route($name).'"', false);
        }
    }

    public function test_home_has_accessible_modal_for_learning_outcome(): void
    {
        $this->get('/')->assertSee('role="dialog"', false)->assertSee('aria-modal="true"', false)->assertSee('Deleng Rinci');
    }

    public function test_home_works_for_logged_in_student_and_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/')->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertOk();
    }

    public function test_every_navbar_route_opens_without_error(): void
    {
        foreach (['home', 'learning.index', 'exercises.index', 'evaluations.index', 'puzzles.index'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_navbar_links_are_real_urls_not_placeholders(): void
    {
        $html = $this->get('/')->getContent();

        foreach (['/ruang-piwulang', '/gladhen-mandiri', '/evaluasi', '/wahana-teka-teki'] as $path) {
            $this->assertStringContainsString('href="'.url($path).'"', $html);
        }
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_coming_soon_pages_have_title_and_way_back(): void
    {
        $this->get('/evaluasi')->assertSee('Evaluasi')->assertSee('Bali menyang Beranda');
        $this->get('/ruang-piwulang')->assertSee('Ruang Piwulang')->assertSee('lagi disiapake');
    }

    public function test_navbar_marks_current_page(): void
    {
        $this->get('/gladhen-mandiri')->assertSee('aria-current="page"', false);
        $this->get('/')->assertSee('aria-current="page"', false);
    }
}
