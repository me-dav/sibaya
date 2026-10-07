<?php

namespace App\Http\Controllers;

use App\Exceptions\CertificateNotAllowed;
use App\Models\Certificate;
use App\Models\EvaluationAttempt;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateService $service)
    {
    }

    /** Terbitkan sertifikat dari attempt milik sendiri yang lulus. */
    public function issue(EvaluationAttempt $attempt): RedirectResponse
    {
        Gate::authorize('view', $attempt); // kepemilikan attempt (404 jika milik orang lain)

        try {
            $certificate = $this->service->issue($attempt);
        } catch (CertificateNotAllowed $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('certificates.show', $certificate);
    }

    /** Halaman cetak. */
    public function show(Certificate $certificate): View
    {
        Gate::authorize('view', $certificate);

        return view('certificates.show', ['certificate' => $certificate] + $this->service->viewData($certificate));
    }

    public function download(Certificate $certificate): StreamedResponse
    {
        Gate::authorize('view', $certificate);

        $path = $this->service->ensurePdf($certificate);

        return Storage::disk($this->service->disk())->download(
            $path, $certificate->certificate_number.'.pdf', ['Content-Type' => 'application/pdf'],
        );
    }

    /** Verifikasi publik lewat nomor / QR. Hanya menampilkan data minimum. */
    public function verify(string $number): View
    {
        $certificate = Certificate::where('certificate_number', $number)->first();

        if (! $certificate) {
            return response()->view('certificates.verify', ['valid' => false, 'number' => $number], 404);
        }

        $data = $this->service->viewData($certificate);

        return view('certificates.verify', [
            'valid' => true, 'number' => $number, 'name' => $data['name'], 'evaluation' => $data['evaluation'],
            'type' => $data['type'], 'predicate' => $data['predicate'], 'issuedAt' => $data['issuedAt'], 'institution' => $data['institution'],
        ]);
    }
}
