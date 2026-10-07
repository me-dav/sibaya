@php
    // Foto hero: taruh di public/images/hero.jpeg. Tanpa foto, dipakai gradasi + pola batik sementara.
    $heroPhoto = file_exists(public_path('images/hero.jpeg')) ? asset('images/hero.jpeg') : null;

    // Teks Capaian Pembelajaran (ganti di sini bila ada revisi).
    $cpJudul = 'Capaian Pembelajaran';
    $cpElemen = 'Elemen Menyimak – Fase E';
    $cpIsi = 'Peserta didik mampu memahami, menginterpretasi, dan mengevaluasi informasi dalam teks lisan berbentuk sastra, budaya, dan kehidupan sehari-hari dengan memperhatikan unsur kebahasaan dan konteks.';

    $cards = [
        [
            'title' => 'Ruang Piwulang', 'subtitle' => 'Materi & Audio Menyimak', 'route' => 'learning.index',
            'icon' => 'headphones', 'deco' => 'book', 'mlebu' => 'text-sibaya-navy-800',
            'box' => 'bg-sky-50 border-sky-200', 'circle' => 'bg-sibaya-navy-800 text-white', 'titleColor' => 'text-sibaya-navy-800', 'tick' => 'text-sibaya-navy-700',
            'items' => ['Rekaman audio lan video sastra Jawa', 'Materi enem jinis Basa Rinengga', 'Tuladha lan rangkuman materi'],
        ],
        [
            'title' => 'Gladhèn Mandiri', 'subtitle' => 'Bank Soal & Kuis Interaktif', 'route' => 'exercises.index',
            'icon' => 'document', 'deco' => 'clipboard', 'mlebu' => 'text-emerald-700',
            'box' => 'bg-emerald-50 border-emerald-200', 'circle' => 'bg-emerald-700 text-white', 'titleColor' => 'text-emerald-900', 'tick' => 'text-emerald-700',
            'items' => ['Pilihan ganda lan isian cekak', 'Analisis teks Basa Rinengga', 'Soal nyimak kanthi audio', 'Nilai lan tinjauan langsung'],
        ],
        [
            'title' => 'Wahana Teka-teki', 'subtitle' => 'Dolanan Edukatif', 'route' => 'puzzles.index',
            'icon' => 'gamepad', 'deco' => 'gamepad', 'mlebu' => 'text-orange-600',
            'box' => 'bg-orange-50 border-orange-200', 'circle' => 'bg-orange-600 text-white', 'titleColor' => 'text-orange-900', 'tick' => 'text-orange-700',
            'items' => ['Teka-teki silang', 'Puzzle susun tembung', 'Skor lan riwayat dolanan'],
        ],
    ];

    $highlights = [
        ['icon' => 'phone', 'title' => 'Akses Gampang', 'text' => 'Bisa dibukak ing kabeh piranti (HP, tablet, laptop).'],
        ['icon' => 'bulb', 'title' => 'Pembelajaran Interaktif', 'text' => 'Audio, visual, lan latihan soal sing narik kawigaten.'],
        ['icon' => 'ornament', 'title' => 'Basis Budaya Jawa', 'text' => 'Nguri-uri basa lan kearifan lokal.'],
        ['icon' => 'chart', 'title' => 'Ndhukung Profil Pelajar Pancasila', 'text' => 'Iman, mandiri, kreatif, gotong royong, lan nalar kritis.'],
    ];
@endphp

<x-layouts.public title="Beranda">

    {{-- ============ HERO ============ --}}
    <section class="relative isolate overflow-hidden" aria-labelledby="judul-beranda"
             x-data="{ cp: false }" @keydown.escape.window="cp = false">

        {{-- Latar kanan: foto (atau placeholder gradasi + pola batik) --}}
        @if ($heroPhoto)
            <img src="{{ $heroPhoto }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover object-right">
        @else
            <div class="absolute inset-0 -z-20 bg-gradient-to-br from-navy-800 via-navy-600 to-sky-300"></div>
            <svg aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full text-gold-400/25">
                <defs>
                    <pattern id="batik" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M24 4 44 24 24 44 4 24Z" fill="none" stroke="currentColor" stroke-width="1.5"/>
                        <circle cx="24" cy="24" r="3" fill="currentColor"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#batik)"/>
            </svg>
        @endif

        <div class="mx-auto grid min-h-[420px] max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.15fr_1fr] lg:py-16">

            <div class="lg:pr-10">
                <p class="text-3xl text-sibaya-navy-800 sm:text-4xl" style="font-family: 'Caveat', cursive;">Sugeng Rawuh ing</p>

                <h1 id="judul-beranda" class="mt-1 flex items-center gap-1 font-display font-extrabold leading-none text-sibaya-navy-800">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sibaya-gold-400 sm:h-20 sm:w-20" aria-hidden="true">
                        <x-icon name="ornament" size="h-12 w-12 sm:h-13 sm:w-13" />
                    </span>
                    <span class="text-6xl sm:text-7xl text-sibaya-navy-800">Si<span class="text-sibaya-navy-800">BAYA</span></span>
                </h1>
                <p class="mt-2 font-display text-xl font-bold text-sibaya-navy-800 sm:text-2xl">(Sinau Basa lan Budaya Jawa)</p>

                <p class="mt-4 max-w-md text-[15px] leading-relaxed text-sibaya-navy-900">
                    Njelajah basa, nguri-uri budaya, nggayuh kawruh kanthi krungu, mangerteni, lan ngaplikasikake.
                </p>

                <p class="mt-5 inline-block -rotate-2 bg-sibaya-gold-300/90 px-5 py-2 text-xl text-sibaya-navy-900 shadow-md" style="font-family: 'Caveat', cursive;">
                    Ayo sinau basa Jawa, ayo jaga budayane!
                </p>
            </div>

            {{-- Kartu Capaian Pembelajaran --}}
            <div class="lg:justify-self-end">
                <div class="max-w-md rounded-3xl bg-white/70 p-6 shadow-xl ring-1 ring-navy-100">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-navy-50 text-sibaya-navy-800"><x-icon name="target" size="h-7 w-7" /></span>
                        <div>
                            <h2 class="font-display text-lg font-extrabold leading-tight text-sibaya-navy-800">{{ $cpJudul }}</h2>
                            <p class="text-sm font-bold text-sibaya-navy-800">{{ $cpElemen }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-sibaya-navy-900">{{ $cpIsi }}</p>
                    <button type="button" @click="cp = true; $nextTick(() => $refs.tutup.focus())"
                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-navy-800 px-5 py-2 text-sm font-bold text-sibaya-navy-900 transition hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
                        Deleng Rinci <x-icon name="arrow-right" size="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal detail Capaian Pembelajaran --}}
        <div x-show="cp" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="cp-judul">
            <div class="absolute inset-0 bg-navy-900/60" @click="cp = false"></div>
            <div x-show="cp" x-transition class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl sm:p-8">
                <h2 id="cp-judul" class="font-display text-2xl font-extrabold text-sibaya-navy-800">{{ $cpJudul }}</h2>
                <p class="mt-1 font-bold text-sibaya-navy-700">{{ $cpElemen }}</p>
                <p class="mt-4 leading-relaxed text-sibaya-navy-900">{{ $cpIsi }}</p>
                <button type="button" x-ref="tutup" @click="cp = false"
                        class="mt-6 rounded-full bg-navy-800 px-6 py-2 text-sm font-bold text-sibaya-navy-800 hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
                    Tutup
                </button>
            </div>
        </div>
    </section>

    {{-- ============ TIGA KARTU FITUR ============ --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6" aria-label="Fitur utama">
        <ul class="grid gap-6 lg:grid-cols-3">
            @foreach ($cards as $card)
                <li class="relative flex flex-col overflow-hidden rounded-3xl border-2 p-6 shadow-md {{ $card['box'] }} hover:-translate-y-4 duration-300 ease-out">
                    <x-icon :name="$card['deco']" size="pointer-events-none absolute -bottom-3 -right-3 h-36 w-36 opacity-40" class="{{ $card['mlebu'] }}" />

                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full {{ $card['circle'] }}"><x-icon :name="$card['icon']" size="h-8 w-8" /></span>
                        <div>
                            <h2 class="font-display text-2xl font-extrabold leading-tight {{ $card['titleColor'] }}">{{ $card['title'] }}</h2>
                            <p class="text-sm font-semibold text-navy-800">{{ $card['subtitle'] }}</p>
                        </div>
                    </div>

                    <ul class="relative mt-5 flex-1 space-y-2 text-[15px] text-navy-900">
                        @foreach ($card['items'] as $item)
                            <li class="flex gap-2"><x-icon name="check" size="mt-0.5 h-5 w-5 shrink-0 {{ $card['tick'] }}" /> {{ $item }}</li>
                        @endforeach
                    </ul>

                    <a href="{{ route($card['route']) }}"
                       class="relative mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-navy-800 px-7 py-2.5 text-sm font-bold {{ $card['mlebu'] }} transition hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2"
                       aria-label="Mlebu {{ $card['title'] }}">
                        Mlebu <x-icon name="arrow-right" size="h-4 w-4" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- ============ STRIP KEUNGGULAN ============ --}}
    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6" aria-label="Keunggulan siBAYA">
        <ul class="grid gap-5 rounded-3xl border border-sky-200 bg-sky-50 p-6 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-sky-200">
            @foreach ($highlights as $h)
                <li class="flex gap-3 lg:px-6 lg:first:pl-0 lg:last:pr-0">
                    <x-icon :name="$h['icon']" size="h-9 w-9 shrink-0 text-sibaya-navy-700" />
                    <div>
                        <h3 class="font-bold text-sibaya-navy-800">{{ $h['title'] }}</h3>
                        <p class="mt-0.5 text-sm text-sibaya-navy-800">{{ $h['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- ============ PITA KUTIPAN (siluet gunung + candi) ============ --}}
    <section aria-label="Kutipan">
        <div class="bg-sibaya-navy-700 px-4 py-2 text-center text-white">
            <p class="mx-auto max-w-xl font-display text-xl italic leading-snug sm:text-2xl">
                Basa Jawa ora mung tembung,<br>nganti jati diri lan warisan budaya.
            </p>
            <div class="mx-auto mt-4 flex max-w-xs items-center justify-center gap-3 text-sibaya-gold-400" aria-hidden="true">
                <span class="h-px flex-1 bg-white/40"></span>
                <x-icon name="ornament" size="h-5 w-5" />
                <span class="h-px flex-1 bg-white/40"></span>
            </div>
        </div>
    </section>
</x-layouts.public>
