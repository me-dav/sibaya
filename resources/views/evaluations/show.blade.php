@php
    use App\Support\Labels;
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
    $statusLabel = ['completed' => 'Rampung', 'expired' => 'Wektu entek', 'in_progress' => 'Lagi dikerjakake', 'abandoned' => 'Ditinggal'];
    $fmtDt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->translatedFormat('d M Y H:i') : null;
@endphp
<x-layouts.public :title="$evaluation->title">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Evaluasi' => route('evaluations.index'), $evaluation->title => null]" />
        <x-flash />

        <article class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200 sm:p-8">
            <span class="rounded-full bg-sibaya-sky-100 px-3 py-1 text-sm font-semibold uppercase text-sibaya-navy-800">{{ Labels::value($evaluation->type) }}</span>
            @unless ($evaluation->is_published)<span class="ml-2 rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-800">Draft &ndash; mung katon kanggo admin</span>@endunless
            <h1 class="mt-3 text-3xl font-extrabold text-sibaya-navy-800">{{ $evaluation->title }}</h1>
            @if ($evaluation->description)<p class="mt-2 text-sibaya-ink/80">{{ $evaluation->description }}</p>@endif

            <dl class="mt-6 grid gap-4 rounded-xl bg-sibaya-sky-100/60 p-4 text-sm sm:grid-cols-4">
                <div><dt class="text-slate-600">Jumlah soal</dt><dd class="text-lg font-bold">{{ $questions }}</dd></div>
                <div><dt class="text-slate-600">Wektu</dt><dd class="text-lg font-bold">{{ $evaluation->duration_minutes }} menit</dd></div>
                <div><dt class="text-slate-600">Nilai lulus</dt><dd class="text-lg font-bold">&ge; {{ $evaluation->passing_score }}</dd></div>
                <div><dt class="text-slate-600">Percobaan</dt><dd class="text-lg font-bold">{{ $attempts->count() }} / {{ $evaluation->max_attempts }}</dd></div>
            </dl>
            @if ($evaluation->start_at || $evaluation->end_at)
                <p class="mt-3 text-sm text-slate-600">Dibuka: {{ $fmtDt($evaluation->start_at) ?? '-' }} &middot; Ditutup: {{ $fmtDt($evaluation->end_at) ?? '-' }}</p>
            @endif

            @if ($evaluation->instructions)
                <section class="mt-6" aria-labelledby="h-petunjuk"><h2 id="h-petunjuk" class="text-lg font-bold text-sibaya-navy-800">Petunjuk</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed">{{ $evaluation->instructions }}</p></section>
            @endif

            <div class="mt-8">
                @guest
                    <a href="{{ route('login') }}" class="inline-block rounded-full bg-sibaya-navy-800 px-6 py-3 font-semibold text-white hover:bg-sibaya-navy-700">Login kanggo miwiti</a>
                @else
                    @can('start', $evaluation)
                        @if ($avail['open'])
                            <a href="{{ route('evaluations.attempts.show', $avail['open']) }}" class="inline-block rounded-full bg-sibaya-navy-800 px-6 py-3 font-semibold text-white hover:bg-sibaya-navy-700">Lanjutake Ngerjakake</a>
                        @elseif ($avail['can'])
                            <form method="POST" action="{{ route('evaluations.start', $evaluation) }}"
                                  data-confirm="Wektu {{ $evaluation->duration_minutes }} menit bakal langsung mlaku lan ora bisa dijeda. Wiwiti saiki?" onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                <button class="rounded-full bg-sibaya-navy-800 px-6 py-3 font-semibold text-white hover:bg-sibaya-navy-700">Wiwiti {{ Labels::value($evaluation->type) === 'uts' ? 'UTS' : 'UAS' }}</button>
                            </form>
                        @else
                            <p role="status" class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">{{ $avail['reason'] }}</p>
                        @endif
                    @else
                        <p class="text-sm text-slate-600">Mung akun siswa aktif sing bisa ngerjakake evaluasi.</p>
                    @endcan
                @endguest
            </div>
        </article>

        @auth
            <section class="mt-8" aria-labelledby="h-riwayat">
                <h2 id="h-riwayat" class="text-xl font-bold text-sibaya-navy-800">Riwayatmu</h2>
                <ul class="mt-3 divide-y divide-slate-100 rounded-2xl bg-white text-sm shadow-sm ring-1 ring-sibaya-sky-200">
                    @forelse ($attempts as $a)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                            <span>Percobaan {{ $loop->remaining + 1 }} &middot; {{ $a->started_at?->translatedFormat('d M Y H:i') }} &middot; {{ $statusLabel[$a->status] ?? $a->status }}</span>
                            @if ($a->status === 'in_progress')
                                <a href="{{ route('evaluations.attempts.show', $a) }}" class="text-sibaya-navy-700 underline">Lanjutake</a>
                            @else
                                <a href="{{ route('evaluations.attempts.result', $a) }}" class="text-sibaya-navy-700 underline">
                                    Nilai {{ $fmt($a->score) }} &middot; {{ $a->score >= $evaluation->passing_score ? '✓ Lulus' : '✗ Durung lulus' }}</a>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-slate-500">Durung ana percobaan.</li>
                    @endforelse
                </ul>
            </section>
        @endauth
    </div>
</x-layouts.public>
