@php
    $link = fn(string $name) => Route::has($name) ? route($name) : '#';
@endphp
<x-layouts.public title="Beranda">

    {{-- HERO --}}
    <section
        class="relative isolate overflow-hidden bg-gradient-to-br from-sibaya-sky-200 via-white to-sibaya-sky-100 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('images/hero.jpeg') }}');" aria-labelledby="judul-hero">

        <div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-12 lg:px-8 lg:py-14">

            <div class="lg:col-span-5">
                <p class="font-script text-3xl text-sibaya-navy-800">
                    Sugeng Rawuh ing
                </p>

                <h1 id="judul-hero" class="mt-1 flex flex-wrap items-center gap-x-3 text-sibaya-navy-800">
                    <x-logo size="lg" class="[&>span:last-child>span:last-child]:hidden" />
                    <span class="sr-only">{{ config('sibaya.name') }}</span>
                </h1>

                <p class="mt-2 text-xl font-semibold text-sibaya-navy-800">
                    (Sinau Basa lan Budaya Jawa)
                </p>

                <p class="mt-4 max-w-md leading-relaxed">
                    Njelajah basa, nguri-uri budaya, nggayuh kawruh kanthi krungu,
                    mangerteni, lan ngaplikasikake.
                </p>

                <p class="brush-gold mt-6">
                    Ayo sinau basa Jawa, ayo jaga budayane!
                </p>
            </div>

            <aside class="lg:col-span-4 lg:col-start-9" aria-labelledby="judul-capaian">
                <div class="rounded-3xl bg-white/50 p-6 shadow-lg ring-1 ring-sibaya-sky-200 backdrop-blur-sm">

                    <div class="flex items-start gap-3">
                        <svg class="h-10 w-10 shrink-0 text-sibaya-navy-800" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <circle cx="12" cy="12" r="5" />
                            <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                        </svg>

                        <h2 id="judul-capaian" class="text-lg font-bold leading-snug text-sibaya-navy-800">
                            Capaian Pembelajaran<br>
                            <span class="text-base">
                                Elemen Menyimak &ndash; Fase E
                            </span>
                        </h2>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed">
                        Peserta didik mampu memahami, menginterpretasi, dan mengevaluasi
                        informasi dalam teks lisan berbentuk sastra, budaya, dan
                        kehidupan sehari-hari dengan memperhatikan unsur kebahasaan
                        dan konteks.
                    </p>

                    <a href="#tentang"
                        class="mt-5 inline-flex items-center gap-2 rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sibaya-navy-700">
                        Lihat Detail

                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>

                </div>
            </aside>

        </div>
    </section>

    {{-- TIGA KARTU NAVIGASI --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" aria-label="Menu utama aplikasi">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <x-nav-card theme="ruang" icon="headphones" title="Ruang Piwulang" subtitle="Materi & Audio Menyimak"
                :href="$link('learning.index')" :items="['Rekaman audio/video sastra Jawa', 'Geguritan, dongeng, piweling, percakapan ngoko-krama', 'Teks transkrip interaktif']" />

            <x-nav-card theme="gladhen" icon="document" title="Gladhen Mandiri" subtitle="Bank Soal & Kuis Interaktif"
                :href="$link('exercises.index')" :items="['Pilihan ganda', 'Mencocokkan', 'Esai singkat berbasis audio']" />

            <x-nav-card theme="wahana" icon="gamepad" title="Wahana Teka-teki" subtitle="Gamification Zone"
                :href="$link('puzzles.index')" :items="['Teka-teki silang', 'Puzzle susun kata', 'Tebak arti sanepa', 'Visual budaya Jawa']" />
        </div>
    </section>

    {{-- KEUNGGULAN --}}
    <section id="tentang" class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8" aria-label="Keunggulan SiBAYA">
        <div
            class="grid gap-6 rounded-3xl shadow-md border border-sibaya-sky-200 bg-sibaya-sky-100/60 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-feature-item icon="phone" title="Akses Mudah">Bisa dibuka di semua perangkat (HP, tablet,
                laptop).</x-feature-item>
            <x-feature-item icon="bulb" title="Pembelajaran Interaktif">Audio, visual, game, dan latihan soal yang
                menarik.</x-feature-item>
            <x-feature-item icon="lotus" title="Berbasis Budaya Jawa">Melestarikan bahasa dan kearifan
                lokal.</x-feature-item>
            <x-feature-item icon="chart" title="Mendukung Profil Pelajar Pancasila">Beriman, berkebinekaan global,
                mandiri, kreatif, gotong royong, bernalar kritis.</x-feature-item>
        </div>
    </section>

</x-layouts.public>