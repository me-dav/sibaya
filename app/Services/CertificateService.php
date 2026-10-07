<?php

namespace App\Services;

use App\Exceptions\CertificateNotAllowed;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Models\User;
use App\Support\Labels;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penerbitan sertifikat (CLAUDE.md bagian 16).
 * Syarat tunggal: attempt sudah selesai dan score >= passing_score evaluasinya.
 * Satu attempt = paling banyak satu sertifikat (unique evaluation_attempt_id).
 */
class CertificateService
{
    /** Sertifikat disimpan di disk private; diunduh lewat route terotorisasi. */
    public function disk(): string
    {
        return config('sibaya.private_disk', 'local');
    }

    public static function predicateFor(float|int|string|null $score): string
    {
        $s = (float) $score;

        return $s >= 90 ? 'Sangat Baik' : ($s >= 80 ? 'Baik' : 'Cukup');
    }

    // ---------------------------------------------------------------- kelayakan

    /** @return array{can: bool, reason: ?string, certificate: ?Certificate} */
    public function statusFor(EvaluationAttempt $attempt): array
    {
        if ($existing = Certificate::where('evaluation_attempt_id', $attempt->id)->first()) {
            return ['can' => true, 'reason' => null, 'certificate' => $existing];
        }

        try {
            $this->assertEligible($attempt);

            return ['can' => true, 'reason' => null, 'certificate' => null];
        } catch (CertificateNotAllowed $e) {
            return ['can' => false, 'reason' => $e->getMessage(), 'certificate' => null];
        }
    }

    private function assertEligible(EvaluationAttempt $attempt): Evaluation
    {
        if (! in_array($attempt->status, ['completed', 'expired'], true)) {
            throw new CertificateNotAllowed('Evaluasi durung rampung, sertifikat durung bisa diterbitake.');
        }

        $evaluation = Evaluation::findOrFail($attempt->evaluation_id);

        if ((float) $attempt->score < (float) $evaluation->passing_score) {
            $score = number_format((float) $attempt->score, 1, ',', '.');
            throw new CertificateNotAllowed("Sertifikat mung kanggo siswa sing lulus (minimal {$evaluation->passing_score}). Nilaimu {$score}.");
        }

        return $evaluation;
    }

    // ---------------------------------------------------------------- penerbitan

    /** Idempotent: pemanggilan ulang mengembalikan sertifikat yang sama. */
    public function issue(EvaluationAttempt $attempt): Certificate
    {
        $certificate = DB::transaction(function () use ($attempt) {
            // Kunci attempt supaya klik ganda tidak membuat dua sertifikat.
            $attempt = EvaluationAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($existing = Certificate::where('evaluation_attempt_id', $attempt->id)->first()) {
                return $existing;
            }

            $evaluation = $this->assertEligible($attempt);

            $certificate = new Certificate();
            $certificate->forceFill([
                'user_id'               => $attempt->user_id,
                'evaluation_attempt_id' => $attempt->id,
                'certificate_number'    => 'TMP-'.Str::uuid(), // diganti segera setelah id diketahui
                'score'                 => $attempt->score,
                'predicate'             => self::predicateFor($attempt->score),
                'issued_at'             => now(),
            ])->save();

            $certificate->forceFill(['certificate_number' => $this->numberFor($certificate, $evaluation)])->save();

            return $certificate;
        });

        $this->ensurePdf($certificate); // di luar transaksi; jika gagal, dicoba lagi saat diunduh

        return $certificate;
    }

    /** SBY-UTS-2026-000123-A9F3K2: unik lewat id, dan sulit ditebak lewat akhiran acak. */
    private function numberFor(Certificate $certificate, Evaluation $evaluation): string
    {
        return sprintf('SBY-%s-%s-%06d-%s',
            strtoupper((string) Labels::value($evaluation->type)), now()->format('Y'), $certificate->id, Str::upper(Str::random(6)));
    }

    // ---------------------------------------------------------------- PDF

    /** Pastikan file PDF ada (dibuat ulang bila hilang) dan kembalikan path-nya. */
    public function ensurePdf(Certificate $certificate): string
    {
        $disk = Storage::disk($this->disk());

        if ($certificate->pdf_path && $disk->exists($certificate->pdf_path)) {
            return $certificate->pdf_path;
        }

        $path = $certificate->pdf_path ?: 'certificates/'.$certificate->certificate_number.'.pdf';

        $disk->put($path, Pdf::loadView('certificates.pdf', $this->viewData($certificate))->setPaper('a4', 'landscape')->output());

        if ($certificate->pdf_path !== $path) {
            $certificate->forceFill(['pdf_path' => $path])->save();
        }

        return $path;
    }

    public function viewData(Certificate $certificate): array
    {
        $attempt = EvaluationAttempt::findOrFail($certificate->evaluation_attempt_id);
        $evaluation = Evaluation::findOrFail($attempt->evaluation_id);
        $user = User::findOrFail($certificate->user_id);
        $verifyUrl = route('certificates.verify', $certificate->certificate_number);

        return [
            'number'      => $certificate->certificate_number,
            'name'        => $user->name,
            'school'      => $user->school_name,
            'class'       => $user->class_name,
            'evaluation'  => $evaluation->title,
            'type'        => strtoupper((string) Labels::value($evaluation->type)),
            'score'       => $certificate->score,
            'passing'     => $evaluation->passing_score,
            'predicate'   => $certificate->predicate ?: self::predicateFor($certificate->score),
            'issuedAt'    => Carbon::parse($certificate->issued_at)->locale('id')->translatedFormat('d F Y'),
            'institution' => config('sibaya.certificate.institution', 'SiBAYA - Sinau Basa lan Budaya Jawa'),
            'signerName'  => config('sibaya.certificate.signer_name', ''),
            'signerTitle' => config('sibaya.certificate.signer_title', 'Admin SiBAYA'),
            'verifyUrl'   => $verifyUrl,
            'qr'          => $this->qrDataUri($verifyUrl),
        ];
    }

    /** QR code bersifat opsional: hanya dibuat bila paket simple-qrcode terpasang. */
    private function qrDataUri(string $url): ?string
    {
        $facade = '\\SimpleSoftwareIO\\QrCode\\Facades\\QrCode';

        if (! class_exists($facade)) {
            return null;
        }

        return 'data:image/svg+xml;base64,'.base64_encode((string) $facade::format('svg')->size(110)->margin(0)->generate($url));
    }

    // ---------------------------------------------------------------- daftar milik siswa

    /**
     * Evaluasi yang sudah lulus: satu entri per evaluasi (attempt dengan nilai tertinggi).
     *
     * @return array<int, array{attempt: EvaluationAttempt, evaluation: Evaluation, certificate: ?Certificate}>
     */
    public function availableFor(User $user): array
    {
        $attempts = EvaluationAttempt::where('user_id', $user->id)->whereIn('status', ['completed', 'expired'])->get();
        $evaluations = Evaluation::whereIn('id', $attempts->pluck('evaluation_id')->unique())->get()->keyBy('id');

        $best = $attempts
            ->filter(fn ($a) => $evaluations->has($a->evaluation_id) && (float) $a->score >= (float) $evaluations[$a->evaluation_id]->passing_score)
            ->groupBy('evaluation_id')
            ->map(fn ($group) => $group->sortByDesc('score')->first());

        $certificates = Certificate::whereIn('evaluation_attempt_id', $best->pluck('id'))->get()->keyBy('evaluation_attempt_id');

        return $best->map(fn ($a) => [
            'attempt'     => $a,
            'evaluation'  => $evaluations[$a->evaluation_id],
            'certificate' => $certificates->get($a->id),
        ])->values()->all();
    }
}
