<x-layouts.public title="Sertifikat">
    <style media="print">
        header, footer, .no-print { display: none !important; }
        body { background: #fff !important; }
        .cert-sheet { box-shadow: none !important; border-width: 6px !important; }
        @page { size: A4 landscape; margin: 10mm; }
    </style>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm text-sibaya-navy-700 hover:underline">&larr; Bali menyang Dashboard</a>
            <div class="flex gap-2">
                <button type="button" onclick="window.print()" class="rounded-full border border-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-sibaya-navy-800 hover:bg-sibaya-sky-100">Cetak</button>
                <a href="{{ route('certificates.download', $certificate) }}" class="rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Unduh PDF</a>
            </div>
        </div>

        <article class="cert-sheet rounded-md border-8 border-double border-sibaya-navy-800 bg-white p-8 text-center shadow-lg sm:p-12" aria-labelledby="judul-sertifikat">
            <p class="text-sm font-semibold uppercase tracking-widest text-sibaya-navy-700">{{ $institution }}</p>
            <h1 id="judul-sertifikat" class="mt-3 text-4xl font-extrabold tracking-[0.3em] text-sibaya-navy-800">SERTIFIKAT</h1>
            <p class="mt-6 text-sm text-slate-600">Diparingake marang</p>
            <p class="mt-2 border-b-2 border-sibaya-gold pb-2 font-script text-5xl text-sibaya-navy-900">{{ $name }}</p>
            <p class="mt-2 text-sm text-slate-600">{{ $school }} @if ($class) &middot; Kelas {{ $class }} @endif</p>

            <p class="mt-6 text-sm">kang wis ngrampungake lan dinyatakake <strong class="text-green-800">LULUS</strong> ing</p>
            <p class="mt-1 text-2xl font-bold text-sibaya-navy-800">{{ $evaluation }} <span class="text-base font-semibold">({{ $type }})</span></p>

            <dl class="mx-auto mt-6 grid max-w-xl grid-cols-3 gap-4 text-sm">
                <div class="rounded-lg bg-sibaya-sky-100 p-3"><dt class="text-slate-600">Nilai</dt><dd class="text-xl font-extrabold">{{ number_format((float) $score, 1, ',', '.') }}</dd></div>
                <div class="rounded-lg bg-sibaya-sky-100 p-3"><dt class="text-slate-600">Predikat</dt><dd class="text-xl font-extrabold">{{ $predicate }}</dd></div>
                <div class="rounded-lg bg-sibaya-sky-100 p-3"><dt class="text-slate-600">Status</dt><dd class="text-xl font-extrabold text-green-800">&#10003; Lulus</dd></div>
            </dl>

            <div class="mt-8 flex flex-wrap items-end justify-between gap-6 text-left text-sm">
                <div>
                    <p>Diterbitake: <strong>{{ $issuedAt }}</strong></p>
                    <p class="mt-1 break-all">Nomor: <strong>{{ $number }}</strong></p>
                    <p class="mt-1 text-xs text-slate-500 break-all">Verifikasi: {{ $verifyUrl }}</p>
                </div>
                <div class="text-center">
                    <p class="text-slate-600">{{ $signerTitle }}</p>
                    <div class="mt-10 border-t border-slate-500 px-6 pt-1 font-semibold">{{ $signerName ?: '..............................' }}</div>
                </div>
                @if ($qr)
                    <img src="{{ $qr }}" alt="QR code verifikasi sertifikat {{ $number }}" class="h-24 w-24">
                @endif
            </div>
        </article>
    </div>
</x-layouts.public>
