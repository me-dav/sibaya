<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesEvaluations;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use MakesEvaluations, MakesMaterials, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function passedAttempt($student, array $evalAttrs = [], float $score = 90)
    {
        $e = $this->makeEvaluation('uts', array_merge(['title' => 'UTS Ganjil', 'passing_score' => 75], $evalAttrs));
        $this->makeMc($e);

        return $this->makeEvalAttempt($student, $e, ['status' => 'completed', 'score' => $score, 'submitted_at' => now()]);
    }

    public function test_siswa_lulus_dapat_menerbitkan_sertifikat_dan_pdf_tersimpan(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s, [], 92);

        $this->actingAs($s)->post(route('certificates.issue', $a))->assertRedirect();

        $c = Certificate::firstOrFail();
        $this->assertSame($s->id, $c->user_id);
        $this->assertEquals(92, (float) $c->score);
        $this->assertSame('Sangat Baik', $c->predicate);
        $this->assertMatchesRegularExpression('/^SBY-UTS-\d{4}-\d{6}-[A-Z0-9]{6}$/', $c->certificate_number);

        $this->assertNotNull($c->pdf_path);
        Storage::disk('local')->assertExists($c->pdf_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($c->pdf_path));
    }

    public function test_siswa_tidak_lulus_tidak_dapat_membuat_sertifikat(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s, ['passing_score' => 75], 60);

        $this->actingAs($s)->post(route('certificates.issue', $a))->assertSessionHas('error');

        $this->assertSame(0, Certificate::count());
    }

    public function test_attempt_yang_belum_selesai_ditolak(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s);
        $a->forceFill(['status' => 'in_progress'])->save();

        $this->actingAs($s)->post(route('certificates.issue', $a))->assertSessionHas('error');
        $this->assertSame(0, Certificate::count());
    }

    public function test_nilai_tepat_sama_dengan_batas_lulus_diterima(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s, ['passing_score' => 75], 75);

        $this->actingAs($s)->post(route('certificates.issue', $a))->assertRedirect();
        $this->assertSame('Cukup', Certificate::firstOrFail()->predicate);
    }

    public function test_penerbitan_idempotent_dan_nomor_unik_antar_sertifikat(): void
    {
        $s = $this->siswa();
        $a1 = $this->passedAttempt($s);
        $a2 = $this->passedAttempt($s);

        $this->actingAs($s)->post(route('certificates.issue', $a1));
        $this->actingAs($s)->post(route('certificates.issue', $a1));
        $this->actingAs($s)->post(route('certificates.issue', $a2));

        $this->assertSame(2, Certificate::count());
        $this->assertSame(2, Certificate::pluck('certificate_number')->unique()->count());
    }

    public function test_siswa_lain_tidak_dapat_menerbitkan_melihat_atau_mengunduh(): void
    {
        $owner = $this->siswa();
        $other = $this->siswa();
        $a = $this->passedAttempt($owner);
        $this->actingAs($owner)->post(route('certificates.issue', $a));
        $c = Certificate::firstOrFail();

        $this->actingAs($other)->post(route('certificates.issue', $a))->assertNotFound();
        $this->actingAs($other)->get(route('certificates.show', $c))->assertNotFound();
        $this->actingAs($other)->get(route('certificates.download', $c))->assertNotFound();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('certificates.show', 1))->assertRedirect(route('login'));
    }

    public function test_halaman_cetak_menampilkan_data_sertifikat(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s, [], 88);
        $this->actingAs($s)->post(route('certificates.issue', $a));
        $c = Certificate::firstOrFail();

        $this->actingAs($s)->get(route('certificates.show', $c))->assertOk()
            ->assertSee($s->name)->assertSee('UTS Ganjil')->assertSee($c->certificate_number)
            ->assertSee('88,0')->assertSee('Baik')->assertSee('Unduh PDF');
    }

    public function test_unduh_pdf_dan_dibuat_ulang_jika_file_hilang(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s);
        $this->actingAs($s)->post(route('certificates.issue', $a));
        $c = Certificate::firstOrFail();

        Storage::disk('local')->delete($c->pdf_path);

        $res = $this->actingAs($s)->get(route('certificates.download', $c))->assertOk();
        $this->assertStringContainsString('application/pdf', $res->headers->get('content-type'));
        Storage::disk('local')->assertExists($c->fresh()->pdf_path);
    }

    public function test_admin_dapat_melihat_sertifikat_siswa(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s);
        $this->actingAs($s)->post(route('certificates.issue', $a));

        $this->actingAs($this->admin())->get(route('certificates.show', Certificate::firstOrFail()))->assertOk();
    }

    public function test_verifikasi_publik_nomor_valid_dan_tidak_valid(): void
    {
        $s = $this->siswa();
        $a = $this->passedAttempt($s);
        $this->actingAs($s)->post(route('certificates.issue', $a));
        $c = Certificate::firstOrFail();

        // Halaman publik: nilai tidak ditampilkan, hanya data minimum.
        $this->get(route('certificates.verify', $c->certificate_number))->assertOk()
            ->assertSee('Sertifikat sah')->assertSee($s->name)->assertDontSee('Nilai');

        $this->get(route('certificates.verify', 'SBY-UTS-2026-000999-XXXXXX'))->assertNotFound()->assertSee('ora ditemokake');
    }

    public function test_tombol_sertifikat_di_halaman_hasil_hanya_untuk_yang_lulus(): void
    {
        $s = $this->siswa();
        $pass = $this->passedAttempt($s, [], 90);
        $fail = $this->passedAttempt($s, ['passing_score' => 95], 90);

        $this->actingAs($s)->get(route('evaluations.attempts.result', $pass))->assertOk()->assertSee('Terbitake Sertifikat');
        $this->actingAs($s)->get(route('evaluations.attempts.result', $fail))->assertOk()
            ->assertDontSee('Terbitake Sertifikat')->assertSee('Sertifikat mung kanggo siswa sing lulus');
    }

    public function test_dashboard_dan_halaman_evaluasi_menampilkan_sertifikat_terbaik_per_evaluasi(): void
    {
        $s = $this->siswa();
        $e = $this->makeEvaluation('uts', ['title' => 'UTS Ganjil', 'passing_score' => 75]);
        $this->makeMc($e);
        $this->makeEvalAttempt($s, $e, ['status' => 'completed', 'score' => 80]);
        $best = $this->makeEvalAttempt($s, $e, ['status' => 'completed', 'score' => 95]);
        $this->makeEvalAttempt($s, $e, ['status' => 'completed', 'score' => 50]);

        $items = app(CertificateService::class)->availableFor($s);
        $this->assertCount(1, $items);
        $this->assertSame($best->id, $items[0]['attempt']->id);

        $this->actingAs($s)->get(route('dashboard'))->assertOk()->assertSee('Terbitake Sertifikat')->assertSee('95,0');
        $this->actingAs($s)->get(route('evaluations.index'))->assertOk()->assertSee('Sertifikat');
    }

    public function test_predikat(): void
    {
        $this->assertSame('Sangat Baik', CertificateService::predicateFor(90));
        $this->assertSame('Baik', CertificateService::predicateFor(89.9));
        $this->assertSame('Baik', CertificateService::predicateFor(80));
        $this->assertSame('Cukup', CertificateService::predicateFor(79.9));
    }
}
