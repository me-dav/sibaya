@php
    use App\Support\Labels; use App\Support\PuzzleRules;
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
    $isScramble = Labels::value($puzzle->puzzle_type) === 'susun_kata';
@endphp
<x-layouts.public :title="$puzzle->title">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Wahana Teka-teki' => route('puzzles.index'), $puzzle->title => null]" />
        <x-flash />

        <article class="mt-6 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-sibaya-wahana-line sm:p-8">
            <span class="rounded-full bg-sibaya-wahana-bg px-3 py-1 text-sm font-semibold text-sibaya-wahana-ink">{{ PuzzleRules::types()[Labels::value($puzzle->puzzle_type)] ?? '' }}</span>
            @unless ($puzzle->is_published)<span class="ml-2 rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-800">Draft &ndash; mung katon kanggo admin</span>@endunless
            <h1 class="mt-3 text-3xl font-extrabold text-sibaya-wahana-ink">{{ $puzzle->title }}</h1>
            @if ($puzzle->description)<p class="mt-2">{{ $puzzle->description }}</p>@endif

            <dl class="mt-6 grid gap-4 rounded-xl bg-sibaya-wahana-bg/60 p-4 text-sm sm:grid-cols-3">
                <div><dt class="text-slate-600">{{ $isScramble ? 'Jumlah soal' : 'Jumlah tembung' }}</dt><dd class="text-lg font-bold">{{ $count }}</dd></div>
                <div><dt class="text-slate-600">Tingkat</dt><dd class="text-lg font-bold">{{ PuzzleRules::difficulties()[$puzzle->difficulty] ?? '-' }}</dd></div>
                <div><dt class="text-slate-600">Wektu</dt><dd class="text-lg font-bold">{{ $limit ? floor($limit / 60).' mnt '.($limit % 60).' dtk' : 'Bebas' }}</dd></div>
            </dl>

            @if ($puzzle->instructions)
                <section class="mt-6" aria-labelledby="h-petunjuk"><h2 id="h-petunjuk" class="text-lg font-bold text-sibaya-navy-800">Petunjuk</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed">{{ $puzzle->instructions }}</p></section>
            @endif
            @if ($limit)<p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">⏱ Wektu mlaku sanalika diwiwiti. Yen telat ngirim, skor dadi 0.</p>@endif

            <div class="mt-8">
                @guest
                    <a href="{{ route('login') }}" class="inline-block rounded-full bg-sibaya-wahana-accent px-6 py-3 font-semibold text-white hover:opacity-90">Login kanggo main</a>
                @else
                    @can('play', $puzzle)
                        <form method="POST" action="{{ route('puzzles.start', $puzzle) }}">@csrf
                            <button class="rounded-full bg-sibaya-wahana-accent px-8 py-3 font-semibold text-white hover:opacity-90">Wiwiti Main</button></form>
                    @else
                        <p class="text-sm text-slate-600">Mung akun siswa aktif sing bisa main puzzle sing wis diterbitake.</p>
                    @endcan
                @endguest
            </div>
        </article>

        @auth
            <section class="mt-8" aria-labelledby="h-riwayat">
                <h2 id="h-riwayat" class="text-xl font-bold text-sibaya-navy-800">Riwayatmu ing puzzle iki</h2>
                <ul class="mt-3 divide-y divide-slate-100 rounded-2xl bg-white text-sm shadow-sm ring-1 ring-sibaya-sky-200">
                    @forelse ($attempts as $a)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                            <span>{{ $a->completed_at?->translatedFormat('d M Y H:i') }} &middot; {{ gmdate('i:s', (int) $a->duration_seconds) }}</span>
                            <a href="{{ route('puzzles.result', $a) }}" class="font-semibold text-sibaya-navy-700 underline">Skor {{ $fmt($a->score) }}</a>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-slate-500">Durung ana percobaan.</li>
                    @endforelse
                </ul>
            </section>
        @endauth
    </div>
</x-layouts.public>
