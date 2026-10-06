<?php

namespace Tests\Feature\Learning;

use App\Support\SafeMarkdown;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class LearningPageTest extends TestCase
{
    use MakesMaterials, RefreshDatabase;

    public function test_index_menampilkan_jenis_dan_link_materi_published(): void
    {
        $this->makeMaterial($this->makeType());

        $this->get(route('learning.index'))
            ->assertOk()
            ->assertSee('Paribasan')
            ->assertSee(route('learning.show', 'materi-paribasan'), false);
    }

    public function test_materi_published_dapat_dilihat_tamu(): void
    {
        $this->makeMaterial($this->makeType());

        $this->get('/ruang-piwulang/materi-paribasan')->assertOk()->assertSee('Teks materi.');
    }

    public function test_materi_draft_tidak_dapat_dilihat_tamu_dan_siswa(): void
    {
        $this->makeMaterial($this->makeType(), ['is_published' => false]);

        $this->get('/ruang-piwulang/materi-paribasan')->assertNotFound();
        $this->actingAs($this->siswa())->get('/ruang-piwulang/materi-paribasan')->assertNotFound();
    }

    public function test_admin_dapat_melihat_draft(): void
    {
        $this->makeMaterial($this->makeType(), ['is_published' => false]);

        $this->actingAs($this->admin())->get('/ruang-piwulang/materi-paribasan')->assertOk();
    }

    public function test_materi_dari_jenis_tidak_published_disembunyikan(): void
    {
        $this->makeMaterial($this->makeType(published: false));

        $this->get('/ruang-piwulang/materi-paribasan')->assertNotFound();
    }

    public function test_html_berbahaya_dibuang_dari_isi(): void
    {
        $this->makeMaterial($this->makeType(), ['content' => '<script>alert(1)</script> [x](javascript:alert(1))']);

        $this->get('/ruang-piwulang/materi-paribasan')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_safe_markdown_membuang_html_mentah(): void
    {
        $this->assertStringNotContainsString('<script', (string) SafeMarkdown::render('<script>x</script>'));
    }

    public function test_video_embed_hanya_untuk_penyedia_yang_diizinkan(): void
    {
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', VideoEmbed::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', VideoEmbed::embedUrl('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('https://player.vimeo.com/video/12345', VideoEmbed::embedUrl('https://vimeo.com/12345'));
        $this->assertNull(VideoEmbed::embedUrl('https://evil.example/watch?v=dQw4w9WgXcQ'));
        $this->assertNull(VideoEmbed::embedUrl('http://youtube.com/watch?v=dQw4w9WgXcQ'));
    }
}
