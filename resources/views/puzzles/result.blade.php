@php $fmt = fn ($n) => number_format((float) $n, 1, ',', '.'); $dur = gmdate('i:s', (int) $attempt->duration_seconds); @endphp
<x-layouts.public title="Hasil {{ $puzzle->title }}">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <x-breadcrumb :items="['Wahana Teka-teki' => route('puzzles.index'), $puzzle->title => route('puzzles.show', $puzzle), 'Hasil' => null]" />

        <section class="mt-6 rounded-3xl bg-white p-8 text-center shadow-sm ring-1 ring-sibaya-wahana-line" aria-labelledby="h-hasil">
            <h1 id="h-hasil" class="text-lg font-semibold text-sibaya-wahana-ink">Hasil {{ $puzzle->title }}</h1>
            @if ($late)<p class="mt-2 inline-block rounded-full bg-amber-100 px-4 py-1 text-sm text-amber-900" role="status">⏱ Wektu entek sadurunge dikirim, mula skor 0.</p>@endif
            <p class="mt-4 text-6xl font-extrabold text-sibaya-wahana-accent">{{ $fmt($attempt->score) }}</p>
            <p class="mt-1 text-sm text-slate-600">saka 100 &middot; wektu {{ $dur }}</p>
            <form method="POST" action="{{ route('puzzles.start', $puzzle) }}" class="mt-6">@csrf
                <button class="rounded-full bg-sibaya-wahana-accent px-6 py-2.5 text-sm font-semibold text-white hover:opacity-90">Coba Maneh</button></form>
        </section>

        @if ($review)
            <section class="mt-8" aria-labelledby="h-rinci">
                <h2 id="h-rinci" class="text-xl font-bold text-sibaya-navy-800">Rincian Jawaban</h2>
                <p class="mt-1 text-sm text-slate-600">Kunci jawaban ora ditampilake supaya bisa dicoba maneh.</p>
                <ul class="mt-4 space-y-3">
                    @foreach ($review as $r)
                        <li class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium">{{ $r['label'] }}: {{ $r['clue'] }}</p>
                                <span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $r['correct'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $r['correct'] ? '✓ Bener' : '✗ Salah' }} &middot; {{ $r['earned'] }}/{{ $r['points'] }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-700">Jawabanmu: <span class="font-mono font-semibold tracking-widest">{{ $r['given'] !== '' ? $r['given'] : '(kosong)' }}</span></p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="mt-8"><a href="{{ route('puzzles.index') }}" class="rounded-full border border-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-sibaya-navy-800 hover:bg-sibaya-sky-100">&larr; Daftar puzzle</a></div>
    </div>
</x-layouts.public>
