<?php

namespace Tests\Feature;

use App\Models\Puzzle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\MakesExercises;
use Tests\Support\MakesMaterials;
use Tests\Support\MakesPuzzles;
use Tests\TestCase;

class PuzzleAdminTest extends TestCase
{
    use MakesExercises, MakesMaterials, MakesPuzzles, RefreshDatabase;

    private function scramblePayload(array $over = []): array
    {
        return array_merge([
            'title' => 'Susun Kata Uji', 'puzzle_type' => 'susun_kata', 'difficulty' => 'mudah', 'is_published' => 1,
            'items' => [['clue' => 'Pitedah siji', 'answer' => 'sanepa', 'points' => 10], ['clue' => 'Pitedah loro', 'answer' => 'Saloka', 'points' => 5]],
        ], $over);
    }

    private function crosswordPayload(array $over = []): array
    {
        return array_merge([
            'title' => 'TTS Uji', 'puzzle_type' => 'teka_teki_silang', 'grid_rows' => 6, 'grid_cols' => 6, 'is_published' => 1,
            'words' => [
                ['answer' => 'saloka', 'clue' => 'Mendatar', 'direction' => 'across', 'row' => 1, 'col' => 1, 'points' => 10],
                ['answer' => 'sanepa', 'clue' => 'Menurun', 'direction' => 'down', 'row' => 1, 'col' => 1, 'points' => 10],
            ],
        ], $over);
    }

    public function test_skema_tabel_sesuai_spesifikasi(): void
    {
        $this->assertTrue(Schema::hasColumns('puzzles', ['id', 'title', 'slug', 'description', 'puzzle_type', 'difficulty', 'instructions', 'configuration', 'is_published', 'created_at', 'updated_at']));
        $this->assertTrue(Schema::hasColumns('puzzle_attempts', ['id', 'user_id', 'puzzle_id', 'score', 'duration_seconds', 'completed_at', 'created_at', 'updated_at']));
    }

    public function test_siswa_tidak_dapat_mengelola_puzzle(): void
    {
        $this->actingAs($this->siswa())->get(route('admin.puzzles.index'))->assertForbidden();
        $this->actingAs($this->siswa())->post(route('admin.puzzles.store'), $this->scramblePayload())->assertForbidden();
    }

    public function test_admin_membuat_susun_kata_dengan_jawaban_dinormalisasi(): void
    {
        $this->actingAs($this->admin())->post(route('admin.puzzles.store'), $this->scramblePayload(['duration_seconds' => 120]))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.puzzles.index'));

        $p = Puzzle::firstOrFail();
        $this->assertSame('susun-kata-uji', $p->slug);
        $this->assertSame(['SANEPA', 'SALOKA'], array_column($p->configuration['items'], 'answer'));
        $this->assertSame(120, $p->configuration['duration_seconds']);
        $this->assertTrue($p->is_published);
    }

    public function test_admin_membuat_teka_teki_silang_valid(): void
    {
        $this->actingAs($this->admin())->post(route('admin.puzzles.store'), $this->crosswordPayload())->assertSessionHasNoErrors();

        $cfg = Puzzle::firstOrFail()->configuration;
        $this->assertSame(6, $cfg['rows']);
        $this->assertCount(2, $cfg['words']);
        $this->assertSame('SALOKA', $cfg['words'][0]['answer']);
    }

    public function test_validasi_jawaban_harus_huruf_dan_jumlah_minimum(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.puzzles.store'), $this->scramblePayload(['items' => [['clue' => 'x', 'answer' => 'abc123', 'points' => 5]]]))
            ->assertSessionHasErrors('items.0.answer');
        $this->actingAs($admin)->post(route('admin.puzzles.store'), $this->scramblePayload(['items' => []]))->assertSessionHasErrors('items');
        $this->actingAs($admin)->post(route('admin.puzzles.store'), $this->scramblePayload(['puzzle_type' => 'lain']))->assertSessionHasErrors('puzzle_type');
        $this->assertSame(0, Puzzle::count());
    }

    public function test_silang_menolak_kata_keluar_kotak_bentrok_dan_tumpang_tindih(): void
    {
        $admin = $this->admin();

        $out = $this->crosswordPayload(['grid_cols' => 4]);   // SALOKA (6 huruf) tidak muat
        $this->actingAs($admin)->post(route('admin.puzzles.store'), $out)->assertSessionHasErrors('words');

        $conflict = $this->crosswordPayload();
        $conflict['words'][1]['answer'] = 'XANEPA';              // sel (1,1) = S vs X
        $this->actingAs($admin)->post(route('admin.puzzles.store'), $conflict)->assertSessionHasErrors('words');

        $overlap = $this->crosswordPayload();
        $overlap['words'][1] = ['answer' => 'LOKA', 'clue' => 'x', 'direction' => 'across', 'row' => 1, 'col' => 3, 'points' => 5]; // searah & tumpang tindih
        $this->actingAs($admin)->post(route('admin.puzzles.store'), $overlap)->assertSessionHasErrors('words');

        $this->assertSame(0, Puzzle::count());
    }

    public function test_slug_harus_unik(): void
    {
        $this->makePuzzle('susun_kata', $this->scrambleConfig(), ['slug' => 'susun-kata-uji']);

        $this->actingAs($this->admin())->post(route('admin.puzzles.store'), $this->scramblePayload(['slug' => 'susun-kata-uji']))->assertSessionHasErrors('slug');
    }

    public function test_jenis_puzzle_tidak_dapat_diubah_saat_update(): void
    {
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());

        $this->actingAs($this->admin())->put(route('admin.puzzles.update', $p), $this->scramblePayload([
            'puzzle_type' => 'teka_teki_silang', 'slug' => $p->slug, 'is_published' => 0,
            'items' => [['clue' => 'Baru', 'answer' => 'PARIBASAN', 'points' => 7]],
        ]))->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('susun_kata', $p->puzzle_type->value);
        $this->assertSame('PARIBASAN', $p->configuration['items'][0]['answer']);
        $this->assertFalse($p->is_published);
    }

    public function test_hapus_diblokir_jika_ada_riwayat_dan_boleh_jika_tidak(): void
    {
        $admin = $this->admin();
        $used = $this->makePuzzle('susun_kata', $this->scrambleConfig());
        $free = $this->makePuzzle('susun_kata', $this->scrambleConfig());
        DB::table('puzzle_attempts')->insert(['user_id' => $this->siswa()->id, 'puzzle_id' => $used->id, 'score' => 50, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->delete(route('admin.puzzles.destroy', $used))->assertSessionHas('error');
        $this->assertDatabaseHas('puzzles', ['id' => $used->id]);

        $this->actingAs($admin)->delete(route('admin.puzzles.destroy', $free))->assertRedirect(route('admin.puzzles.index'));
        $this->assertDatabaseMissing('puzzles', ['id' => $free->id]);
    }

    public function test_admin_melihat_riwayat_percobaan(): void
    {
        $s = $this->siswa();
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());
        DB::table('puzzle_attempts')->insert(['user_id' => $s->id, 'puzzle_id' => $p->id, 'score' => 66.67, 'duration_seconds' => 75,
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->admin())->get(route('admin.puzzles.attempts', $p))->assertOk()->assertSee($s->name)->assertSee('66,7')->assertSee('01:15');
    }

    public function test_siswa_dengan_riwayat_puzzle_tidak_dapat_dihapus(): void
    {
        $s = $this->siswa();
        $p = $this->makePuzzle('susun_kata', $this->scrambleConfig());
        DB::table('puzzle_attempts')->insert(['user_id' => $s->id, 'puzzle_id' => $p->id, 'score' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->admin())->delete(route('admin.students.destroy', $s))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $s->id]);
    }
}
