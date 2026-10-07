<?php

namespace Tests\Feature;

use App\Models\PuzzleAttempt;
use App\Services\PuzzleService;
use App\Support\Crossword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\Support\MakesPuzzles;
use Tests\TestCase;

class PuzzlePlayTest extends TestCase
{
    use MakesExercises, MakesMaterials, MakesPuzzles, RefreshDatabase;

    private function begin($student, $puzzle): PuzzleAttempt
    {
        $this->actingAs($student)->post(route('puzzles.start', $puzzle))->assertRedirect();

        return PuzzleAttempt::latest('id')->firstOrFail();
    }

    public function test_index_hanya_menampilkan_puzzle_terbit_dan_bisa_difilter(): void
    {
        $this->makePuzzle('susun_kata', $this->scrambleConfig(), ['title' => 'Susun Terbit']);
        $this->makePuzzle('teka_teki_silang', $this->crosswordConfig(), ['title' => 'Silang Terbit']);
        $this->makePuzzle('susun_kata', $this->scrambleConfig(), ['title' => 'Susun Draft', 'is_published' => false]);

        $this->get(route('puzzles.index'))->assertOk()->assertSee('Susun Terbit')->assertSee('Silang Terbit')->assertDontSee('Susun Draft');
        $this->get(route('puzzles.index', ['type' => 'susun_kata']))->assertSee('Susun Terbit')->assertDontSee('Silang Terbit');
    }

    public function test_draft_404_untuk_siswa_tetapi_admin_bisa_melihat(): void
    {
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig(), ['is_published' => false]);

        $this->actingAs($this->siswa())->get(route('puzzles.show', $p))->assertNotFound();
        $this->actingAs($this->admin())->get(route('puzzles.show', $p))->assertOk();
    }

    public function test_mulai_main_butuh_login_dan_hanya_siswa_aktif(): void
    {
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());

        $this->post(route('puzzles.start', $p))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->post(route('puzzles.start', $p))->assertForbidden();

        $draft = $this->makePuzzle('susun_kata', $this->scrambleConfig(), ['is_published' => false]);
        $this->actingAs($this->siswa())->post(route('puzzles.start', $draft))->assertForbidden();

        $s = $this->siswa();
        $this->actingAs($s)->post(route('puzzles.start', $p))->assertRedirect();
        $this->assertDatabaseHas('puzzle_attempts', ['user_id' => $s->id, 'puzzle_id' => $p->id, 'completed_at' => null]);
    }

    public function test_halaman_main_susun_kata_tidak_membocorkan_jawaban(): void
    {
        $s = $this->siswa();
        $a = $this->begin($s, $this->makePuzzle('susun_kata', $this->scrambleConfig()));

        $this->actingAs($s)->get(route('puzzles.play', $a))->assertOk()
            ->assertSee('Unen-unen kang ora langsung.')
            ->assertDontSee('SANEPA', false)->assertDontSee('SALOKA', false)->assertDontSee('BEBASAN', false);
    }

    public function test_halaman_main_silang_tidak_membocorkan_jawaban_dan_menampilkan_kotak(): void
    {
        $s = $this->siswa();
        $a = $this->begin($s, $this->makePuzzle('teka_teki_silang', $this->crosswordConfig()));

        $html = $this->actingAs($s)->get(route('puzzles.play', $a))->assertOk()->assertSee('Pitedah mendatar.')->assertSee('Pitedah menurun.')->getContent();

        $this->assertStringNotContainsString('SALOKA', $html);
        $this->assertStringNotContainsString('SANEPA', $html);
        $this->assertSame(11, substr_count($html, 'name="cells[')); // 6 + 6 - 1 sel bersama
    }

    public function test_acakan_deterministik_dan_tidak_sama_dengan_kata_asli(): void
    {
        foreach (['SANEPA', 'BEBASAN', 'AB', 'PEPINDHAN'] as $word) {
            $one = PuzzleService::scramble($word, 42);
            $this->assertSame($one, PuzzleService::scramble($word, 42));
            $this->assertNotSame(mb_str_split($word), $one);
            $this->assertSame(collect(mb_str_split($word))->sort()->values()->all(), collect($one)->sort()->values()->all());
        }
        $this->assertSame(['A', 'A'], PuzzleService::scramble('AA', 1));
    }

    public function test_penilaian_susun_kata_di_server_dan_riwayat_tersimpan(): void
    {
        $s = $this->siswa();
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());
        $a = $this->begin($s, $p);

        // 2 dari 3 benar (huruf kecil/spasi dinormalisasi). "score" dari klien harus diabaikan.
        $this->actingAs($s)->post(route('puzzles.submit', $a), ['score' => 100, 'items' => [0 => 'sanepa', 1 => ' salo ka ', 2 => 'salah']])
            ->assertRedirect(route('puzzles.result', $a));

        $a->refresh();
        $this->assertEquals(66.67, (float) $a->score);
        $this->assertNotNull($a->completed_at);
        $this->assertNotNull($a->duration_seconds);

        $this->actingAs($s)->get(route('puzzles.result', $a))->assertOk()->assertSee('66,7');
        $this->actingAs($s)->get(route('puzzles.index'))->assertSee('66,7');
        $this->actingAs($s)->get(route('puzzles.show', $p))->assertSee('66,7');
    }

    public function test_penilaian_silang_per_kata(): void
    {
        $s = $this->siswa();
        $cfg = $this->crosswordConfig();
        $a = $this->begin($s, $this->makePuzzle('teka_teki_silang', $cfg));

        $this->actingAs($s)->post(route('puzzles.submit', $a), ['cells' => $this->cellsFor($cfg, ['SALOKA'])]);

        $this->assertEquals(50, (float) $a->fresh()->score); // hanya SALOKA yang lengkap
    }

    public function test_semua_benar_100_dan_kosong_0(): void
    {
        $s = $this->siswa();
        $cfg = $this->crosswordConfig();
        $p = $this->makePuzzle('teka_teki_silang', $cfg);

        $full = $this->begin($s, $p);
        $this->actingAs($s)->post(route('puzzles.submit', $full), ['cells' => $this->cellsFor($cfg, ['SALOKA', 'SANEPA'])]);
        $this->assertEquals(100, (float) $full->fresh()->score);

        $empty = $this->begin($s, $p);
        $this->actingAs($s)->post(route('puzzles.submit', $empty), []);
        $this->assertEquals(0, (float) $empty->fresh()->score);
        $this->assertNotNull($empty->fresh()->completed_at);
    }

    public function test_rincian_hanya_tampil_sekali_setelah_kirim(): void
    {
        $s = $this->siswa();
        $a = $this->begin($s, $this->makePuzzle('susun_kata', $this->scrambleConfig()));

        $this->actingAs($s)->followingRedirects()->post(route('puzzles.submit', $a), ['items' => [0 => 'SANEPA']])
            ->assertSee('Rincian Jawaban')->assertSee('✓ Bener')->assertDontSee('SALOKA');

        $this->actingAs($s)->get(route('puzzles.result', $a))->assertOk()->assertDontSee('Rincian Jawaban');
    }

    public function test_kirim_dua_kali_tidak_menilai_ulang(): void
    {
        $s = $this->siswa();
        $a = $this->begin($s, $this->makePuzzle('susun_kata', $this->scrambleConfig()));

        $this->actingAs($s)->post(route('puzzles.submit', $a), ['items' => [0 => 'SANEPA', 1 => 'SALOKA', 2 => 'BEBASAN']]);
        $first = $a->fresh();
        $this->actingAs($s)->post(route('puzzles.submit', $a), ['items' => [0 => 'x']]);

        $this->assertEquals(100, (float) $a->fresh()->score);
        $this->assertEquals($first->completed_at, $a->fresh()->completed_at);
    }

    public function test_puzzle_berwaktu_kiriman_terlambat_bernilai_nol(): void
    {
        $s = $this->siswa();
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig(60));
        $answers = ['items' => [0 => 'SANEPA', 1 => 'SALOKA', 2 => 'BEBASAN']];

        $onTime = $this->begin($s, $p);
        $this->travel(30)->seconds();
        $this->actingAs($s)->post(route('puzzles.submit', $onTime), $answers);
        $this->assertEquals(100, (float) $onTime->fresh()->score);

        $this->travelBack();
        $late = $this->begin($s, $p);
        $this->travel(2)->minutes();
        $this->actingAs($s)->post(route('puzzles.submit', $late), $answers);

        $late->refresh();
        $this->assertEquals(0, (float) $late->score);
        $this->assertSame(60, (int) $late->duration_seconds);
    }

    public function test_percobaan_siswa_lain_tidak_dapat_diakses(): void
    {
        $owner = $this->siswa();
        $other = $this->siswa();
        $a = $this->begin($owner, $this->makePuzzle('susun_kata', $this->scrambleConfig()));

        $this->actingAs($other)->get(route('puzzles.play', $a))->assertNotFound();
        $this->actingAs($other)->post(route('puzzles.submit', $a), [])->assertNotFound();
        $this->actingAs($other)->get(route('puzzles.result', $a))->assertNotFound();
        $this->assertNull($a->fresh()->completed_at);
    }

    public function test_main_ulang_tanpa_batas_semua_percobaan_tersimpan(): void
    {
        $s = $this->siswa();
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());

        foreach (range(1, 3) as $n) {
            $a = $this->begin($s, $p);
            $this->actingAs($s)->post(route('puzzles.submit', $a), ['items' => [0 => 'SANEPA']]);
        }

        $this->assertSame(3, PuzzleAttempt::where('user_id', $s->id)->whereNotNull('completed_at')->count());
    }

    public function test_penomoran_dan_validasi_tata_letak_silang(): void
    {
        $layout = Crossword::layout($this->crosswordConfig());

        $this->assertSame([], $layout['errors']);
        $this->assertSame(['0-0' => 1], $layout['numbers']); // across + down berbagi nomor 1

        $public = Crossword::publicLayout($layout);
        $this->assertSame(11, count($public['active']));
        $this->assertArrayNotHasKey('answer', $public['clues']['across'][0]);
    }
}
