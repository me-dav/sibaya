@php
    use Illuminate\Support\Carbon;
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
    $statusLabel = ['completed' => 'Rampung', 'expired' => 'Wektu entek', 'in_progress' => 'Lagi dikerjakake', 'abandoned' => 'Ditinggal'];
    $sections = ['UTS' => ['Ujian Tengah Semester', 'Pilihan ganda', $uts], 'UAS' => ['Ujian Akhir Semester', 'Isian singkat', $uas]];
@endphp
<x-layouts.public title="Evaluasi">
    <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
        <x-hero badge="Evaluasi" title="UTS lan UAS" subtitle="Basa Rinengga Kelas X SMA">
            Ukur pemahamanmu. Kerjakan UTS (pilihan ganda) lan UAS (isian) sajrone wektu sing wis ditemtokake.
        </x-hero>

        <x-flash />

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($sections as $code => [$name, $format, $list])
                <section class="rounded-2xl border border-sibaya-ruang-line bg-sibaya-ruang-bg p-6" aria-labelledby="h-{{ $code }}">
                    <h2 id="h-{{ $code }}" class="text-xl font-bold text-sibaya-navy-800">{{ $code }} <span class="text-base font-medium">&ndash; {{ $name }}</span></h2>
                    <p class="text-sm text-sibaya-ink/70">Jinis soal: {{ $format }}</p>

                    <ul class="mt-4 space-y-3">
                        @forelse ($list as $e)
                            @php $i = $info[$e->id]; @endphp
                            <li class="rounded-xl bg-white p-4 shadow-sm">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold">{{ $e->title }}</p>
                                        <p class="text-xs text-slate-600">{{ $i['questions'] }} soal &middot; {{ $e->duration_minutes }} menit &middot; lulus &ge; {{ $e->passing_score }} &middot; percobaan {{ $i['used'] }}/{{ $e->max_attempts }}</p>
                                        @if ($i['best'] !== null)<p class="mt-1 text-sm">Nilai paling dhuwur: <strong>{{ $fmt($i['best']) }}</strong></p>@endif
                                        @unless ($i['avail']['can'])<p class="mt-1 text-sm text-amber-800">{{ $i['avail']['reason'] }}</p>@endunless
                                    </div>
                                    <a href="{{ route('evaluations.show', $e) }}" class="rounded-full bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">
                                        {{ $i['avail']['open'] ? 'Lanjutake' : 'Detail' }}
                                    </a>
                                </div>
                            </li>
                        @empty
                            <li class="rounded-xl bg-white p-6 text-center text-sm text-slate-500">Durung ana {{ $code }} sing diterbitake.</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-cara">
            <h2 id="h-cara" class="text-xl font-bold text-sibaya-navy-800">Carane Melu Tes</h2>
            <ol class="mt-3 list-decimal space-y-1 pl-6 text-sm leading-relaxed">
                <li>Mlebu (login) nganggo akun siswa.</li>
                <li>Pilih UTS utawa UAS, banjur klik <strong>Detail</strong> lan <strong>Wiwiti</strong>.</li>
                <li>Wektu mlaku sanalika tes diwiwiti lan ora bisa dijeda. Jawabanmu kasimpen otomatis.</li>
                <li>Klik <strong>Kirim</strong> yen wis rampung. Yen wektu entek, jawaban sing wis kasimpen dikirim otomatis.</li>
                <li>Nilai lan status lulus katon sanalika sawise kekirim. Kunci jawaban ora ditampilake.</li>
            </ol>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-nilai">
                <h2 id="h-nilai" class="text-xl font-bold text-sibaya-navy-800">Nilai lan Skor</h2>
                @auth
                    @php $best = collect($info)->filter(fn ($i) => $i['best'] !== null); @endphp
                    <ul class="mt-3 divide-y divide-slate-100 text-sm">
                        @forelse ($uts->concat($uas)->filter(fn ($e) => $info[$e->id]['best'] !== null) as $e)
                            <li class="flex justify-between py-2"><span>{{ $e->title }}</span><span class="font-semibold">{{ $fmt($info[$e->id]['best']) }}</span></li>
                        @empty
                            <li class="py-4 text-slate-500">Durung ana nilai.</li>
                        @endforelse
                    </ul>
                @else
                    <p class="mt-3 text-sm text-slate-600"><a href="{{ route('login') }}" class="text-sibaya-navy-700 underline">Login</a> kanggo ndeleng nilaimu.</p>
                @endauth
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-riwayat">
                <h2 id="h-riwayat" class="text-xl font-bold text-sibaya-navy-800">Riwayat Evaluasi</h2>
                @auth
                    <ul class="mt-3 divide-y divide-slate-100 text-sm">
                        @forelse ($history as $a)
                            @php
                                $ev = $titles->get($a->evaluation_id);
                                $st = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status;
                            @endphp
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                                <span>{{ $ev?->title }} <span class="text-xs text-slate-500">&middot; {{ $a->started_at?->translatedFormat('d M Y H:i') }} &middot; {{ $statusLabel[$st] ?? $st }}</span></span>
                                @if ($st === 'in_progress')
                                    <a href="{{ route('evaluations.attempts.show', $a) }}" class="text-sibaya-navy-700 underline">Lanjutake</a>
                                @else
                                    <a href="{{ route('evaluations.attempts.result', $a) }}" class="text-sibaya-navy-700 underline">Nilai {{ $fmt($a->score) }}</a>
                                @endif
                            </li>
                        @empty
                            <li class="py-4 text-slate-500">Durung ana riwayat.</li>
                        @endforelse
                    </ul>
                @else
                    <p class="mt-3 text-sm text-slate-600">Riwayat katon sawise login.</p>
                @endauth
            </section>
        </div>

        @auth
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-rekom">
                <h2 id="h-rekom" class="text-xl font-bold text-sibaya-navy-800">Rekomendasi Materi</h2>
                <div class="mt-3"><x-recommendation-list :summary="$recommendations" /></div>
            </section>
        @endauth

        @auth
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-sertifikat">
                <h2 id="h-sertifikat" class="text-xl font-bold text-sibaya-navy-800">Sertifikat</h2>
                <div class="mt-3"><x-certificate-list :items="$certificates" /></div>
            </section>
        @endauth
    </div>
</x-layouts.public>
