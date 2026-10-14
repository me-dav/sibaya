@php
    use App\Support\Labels;
    use App\Support\PuzzleRules;
    $fmt = fn($n) => number_format((float) $n, 1, ',', '.');
    $diff = PuzzleRules::difficulties();
@endphp
<x-layouts.public title="Wahana Teka-teki">
    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <x-hero badge="Wahana Teka-teki" title="Dolanan Basa Rinengga"
            subtitle="Teka-teki silang lan puzzle susun kata">
            <p class="bg-sky-100/80 p-2 rounded-lg ring-1 ring-sibaya-navy-200 backdrop-blur-sm">Sinau karo dolanan!
                Selesaikan teka-teki, kumpulna skor paling dhuwur, lan ulangi sepira kerepe.</p>
        </x-hero>
        <x-flash />

        <nav aria-label="Saring jinis puzzle" class="flex flex-wrap gap-2">
            <a href="{{ route('puzzles.index') }}" @if (!$type) aria-current="page" @endif
                class="rounded-full px-5 py-2 text-sm font-semibold ring-1 ring-sibaya-wahana-line {{ !$type ? 'bg-sibaya-wahana-accent text-white' : 'bg-white text-sibaya-wahana-ink hover:bg-sibaya-wahana-bg' }}">Kabeh</a>
            @foreach (PuzzleRules::types() as $v => $l)
                <a href="{{ route('puzzles.index', ['type' => $v]) }}" @if ($type === $v) aria-current="page" @endif
                    class="rounded-full px-5 py-2 text-sm font-semibold ring-1 ring-sibaya-wahana-line {{ $type === $v ? 'bg-sibaya-wahana-accent text-white' : 'bg-white text-sibaya-wahana-ink hover:bg-sibaya-wahana-bg' }}">{{ $l }}</a>
            @endforeach
        </nav>

        <section aria-label="Daftar puzzle" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($puzzles as $p)
                @php $s = $stats->get($p->id);
                $limit = $p->configuration['duration_seconds'] ?? null; @endphp
                <article
                    class="flex flex-col rounded-2xl border border-sibaya-wahana-line bg-sibaya-wahana-bg p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sibaya-wahana-ink">
                        {{ PuzzleRules::types()[Labels::value($p->puzzle_type)] ?? '' }}</p>
                    <h2 class="mt-1 text-xl font-bold text-sibaya-wahana-ink">{{ $p->title }}</h2>
                    @if ($p->description)
                        <p class="mt-2 flex-1 text-sm leading-relaxed">
                    {{ \Illuminate\Support\Str::limit($p->description, 120) }}</p>@else<div class="flex-1"></div>@endif
                    <p class="mt-3 text-xs text-slate-700">
                        @if ($p->difficulty){{ $diff[$p->difficulty] ?? $p->difficulty }} &middot; @endif
                        {{ $limit ? 'Wektu ' . floor($limit / 60) . ' menit ' . ($limit % 60 ? ($limit % 60) . ' detik' : '') : 'Tanpa watesan wektu' }}
                    </p>
                    @if ($s)
                        <p class="mt-1 text-sm">Skor paling dhuwur: <strong>{{ $fmt($s['best']) }}</strong> <span
                    class="text-xs text-slate-600">({{ $s['count'] }}x main)</span></p>@endif
                    <a href="{{ route('puzzles.show', $p) }}"
                        class="mt-4 inline-flex w-fit items-center gap-2 rounded-full bg-sibaya-navy-800 px-6 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Detail
                        <span aria-hidden="true">&rarr;</span></a>
                </article>
            @empty
                <p
                    class="col-span-full rounded-2xl border border-dashed border-sibaya-wahana-line p-10 text-center text-slate-600">
                    Durung ana puzzle sing diterbitake.</p>
            @endforelse
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-riwayat">
            <h2 id="h-riwayat" class="text-xl font-bold text-sibaya-navy-800">Riwayat Dolananmu</h2>
            @auth
                <ul class="mt-3 divide-y divide-slate-100 text-sm">
                    @forelse ($history as $a)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span>{{ $titles[$a->puzzle_id] ?? '-' }}<br><span
                                    class="text-xs text-slate-500">{{ $a->completed_at?->translatedFormat('d M Y H:i') }}
                                    &middot; {{ gmdate('i:s', (int) $a->duration_seconds) }}</span></span>
                            <a href="{{ route('puzzles.result', $a) }}"
                                class="font-semibold text-sibaya-navy-700 underline">Skor {{ $fmt($a->score) }}</a>
                        </li>
                    @empty
                        <li class="py-4 text-slate-500">Durung ana riwayat. Pilih puzzle lan miwiti!</li>
                    @endforelse
                </ul>
            @else
                <p class="mt-3 text-sm text-slate-600"><a href="{{ route('login') }}"
                        class="text-sibaya-navy-700 underline">Login</a> kanggo nyimpen skor lan riwayat.</p>
            @endauth
        </section>
    </div>
</x-layouts.public>