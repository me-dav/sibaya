<x-layouts.public title="Verifikasi Sertifikat">
    <div class="mx-auto max-w-xl px-4 py-12 sm:px-6">
        <section class="rounded-3xl bg-white p-8 text-center shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-verif">
            <h1 id="h-verif" class="text-2xl font-bold text-sibaya-navy-800">Verifikasi Sertifikat</h1>
            @if ($valid)
                <p class="mt-4 inline-block rounded-full bg-green-100 px-5 py-1.5 text-sm font-bold text-green-800" role="status">&#10003; Sertifikat sah</p>
                <dl class="mt-6 space-y-3 text-left text-sm">
                    <div><dt class="text-slate-500">Diparingake marang</dt><dd class="text-lg font-semibold">{{ $name }}</dd></div>
                    <div><dt class="text-slate-500">Evaluasi</dt><dd class="font-semibold">{{ $evaluation }} ({{ $type }})</dd></div>
                    <div><dt class="text-slate-500">Predikat</dt><dd class="font-semibold">{{ $predicate }}</dd></div>
                    <div><dt class="text-slate-500">Diterbitake</dt><dd class="font-semibold">{{ $issuedAt }}</dd></div>
                    <div><dt class="text-slate-500">Nomor</dt><dd class="break-all font-mono text-xs">{{ $number }}</dd></div>
                    <div><dt class="text-slate-500">Penerbit</dt><dd class="font-semibold">{{ $institution }}</dd></div>
                </dl>
            @else
                <p class="mt-4 inline-block rounded-full bg-red-100 px-5 py-1.5 text-sm font-bold text-red-800" role="alert">&#10007; Sertifikat ora ditemokake</p>
                <p class="mt-4 text-sm text-slate-600">Nomor <span class="break-all font-mono">{{ $number }}</span> ora cocog karo sertifikat apa wae. Priksa maneh nomor utawa QR code-e.</p>
            @endif
        </section>
    </div>
</x-layouts.public>
