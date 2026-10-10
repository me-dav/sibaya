@php
    $heroPhoto = file_exists(public_path('images/hero-gladhen.jpg')) ? asset('images/hero-gladhen.jpg') : null;
    $done = $summary['completed_exercises'] ?? 0;
    $total = $summary['total_exercises'] ?? 0;
    $ratio = $total > 0 ? min(1, $done / $total) : 0;
    $circ = 2 * M_PI * 34;
    $best = $summary['best_score'] ?? null;
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

<x-exercise.shell title="Gladhèn Mandiri" :active="$activeType">

    {{-- HERO --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-50 via-sky-50 to-sky-200 shadow-sm ring-1 ring-navy-100" aria-labelledby="judul-gladhen">
        @if ($heroPhoto)
            <img src="{{ $heroPhoto }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-cream-50/95 via-cream-50/70 to-transparent"></div>
        @else
            {{-- Placeholder sampai foto hero tersedia: public/images/hero-gladhen.jpg --}}
            <svg aria-hidden="true" class="absolute bottom-0 right-0 h-full w-2/3 text-sky-300/50" viewBox="0 0 400 200" preserveAspectRatio="xMaxYMax slice" fill="currentColor">
                <path d="M0 200V150c40-20 80-60 120-60s60 30 100 30 70-50 110-50 50 20 70 30v100z"/>
                <path d="M200 200v-30h-24v-20h18v-24h22v-26h14v-30h14v30h14v26h22v24h18v20h-24v30z" opacity=".7"/>
            </svg>
        @endif

        <div class="relative grid gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_340px] lg:items-center">
            <div>
                <span class="inline-block rounded-full bg-navy-800 px-4 py-1 text-xs font-bold text-white">Gladhèn Mandiri</span>
                <h1 id="judul-gladhen" class="mt-3 font-display text-4xl font-extrabold leading-tight text-navy-800 sm:text-5xl">Basa Rinengga</h1>
                <p class="font-display text-2xl font-bold text-navy-800">Kelas X SMA</p>
                <p class="mt-3 max-w-xl text-[15px] text-navy-900">
                    Ayo nguji pemahamanmu babagan Basa Rinengga kanthi latihan interaktif sing variatif.
                    Titilabaike progres lan rai skor paling apikmu!
                </p>
            </div>

            {{-- Progres --}}
            <div class="rounded-2xl bg-white p-5 shadow-lg ring-1 ring-navy-100">
                <h2 class="font-display text-lg font-extrabold text-navy-800">Progres Latihan</h2>

                @if ($summary !== null)
                    <div class="mt-3 flex items-center gap-4">
                        <div class="relative h-20 w-20 shrink-0" role="img" aria-label="{{ $done }} saka {{ $total }} latihan rampung">
                            <svg viewBox="0 0 80 80" class="h-full w-full -rotate-90">
                                <circle cx="40" cy="40" r="34" fill="none" stroke="#dce7f6" stroke-width="8"/>
                                <circle cx="40" cy="40" r="34" fill="none" stroke="#1f4580" stroke-width="8" stroke-linecap="round"
                                        stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ * (1 - $ratio) }}"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center font-display text-lg font-extrabold text-navy-800">{{ $done }}/{{ $total }}</span>
                        </div>
                        <p class="font-semibold text-navy-800">Latihan Rampung</p>
                    </div>
                    <div class="mt-4 border-t border-navy-100 pt-3 text-center">
                        <p class="font-display text-2xl font-extrabold text-navy-800">{{ $best !== null ? $fmt($best) : '-' }}</p>
                        <p class="text-xs text-navy-600">Skor Tertinggi</p>
                    </div>
                @else
                    <p class="mt-2 text-sm text-navy-700">Mlebu supaya progres lan skormu kecathet.</p>
                    <a href="{{ route('login') }}" class="mt-3 inline-flex rounded-full bg-navy-800 px-5 py-2 text-sm font-bold text-white hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500">Mlebu</a>
                @endif
            </div>
        </div>
    </section>

    {{-- JENIS LATIHAN --}}
    <section class="mt-8" aria-labelledby="jenis-latihan">
        <h2 id="jenis-latihan" class="font-display text-2xl font-extrabold text-navy-800">Pilih Jenis Latihan</h2>
        <ul class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($types as $type)
                @php $t = $type->theme(); $on = $activeType === $type; @endphp
                <li>
                    <a href="{{ route('exercises.index', ['jenis' => $type->value]) }}" @if ($on) aria-current="true" @endif
                       class="group flex h-full flex-col rounded-2xl border-2 p-4 shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500 {{ $t['card'] }} {{ $on ? 'ring-2 ring-navy-500' : '' }}">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full {{ $t['icon'] }}"><x-icon :name="$type->icon()" size="h-6 w-6" /></span>
                        <span class="mt-3 font-display text-lg font-extrabold leading-tight {{ $t['text'] }}">{{ $type->label() }}</span>
                        <span class="mt-1 flex-1 text-sm text-navy-800">{{ $type->description() }}</span>
                        <span class="mt-3 flex justify-end"><span class="flex h-7 w-7 items-center justify-center rounded-full {{ $t['soft'] }} transition group-hover:translate-x-0.5"><x-icon name="arrow-right" size="h-4 w-4" /></span></span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- DAFTAR + SAMPING --}}
    <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_300px]">
        <section aria-labelledby="daftar-latihan" class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="daftar-latihan" class="font-display text-2xl font-extrabold text-navy-800">Daftar Latihan</h2>
                <p class="rounded-lg border border-navy-100 bg-white px-3 py-1.5 text-xs text-navy-700">Urutake miturut: Terbaru</p>
            </div>

            <nav class="mt-3 flex gap-1 overflow-x-auto rounded-2xl bg-navy-50 p-1.5" aria-label="Filter jenis latihan">
                <a href="{{ route('exercises.index') }}" @if (! $activeType) aria-current="page" @endif
                   class="whitespace-nowrap rounded-xl px-4 py-1.5 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500 {{ ! $activeType ? 'bg-navy-800 text-white' : 'text-navy-700 hover:bg-white' }}">Kabeh</a>
                @foreach ($types as $type)
                    <a href="{{ route('exercises.index', ['jenis' => $type->value]) }}" @if ($activeType === $type) aria-current="page" @endif
                       class="whitespace-nowrap rounded-xl px-4 py-1.5 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500 {{ $activeType === $type ? 'bg-navy-800 text-white' : 'text-navy-700 hover:bg-white' }}">{{ str_replace('Latihan ', '', $type->label()) }}</a>
                @endforeach
            </nav>

            @if ($exercises->isEmpty())
                <div class="mt-4 rounded-2xl border border-dashed border-navy-200 bg-white p-8 text-center">
                    <p class="font-display text-lg font-bold text-navy-800">Durung ana latihan</p>
                    <p class="mt-1 text-sm text-navy-700">Latihan kanggo jenis iki durung kasedhiya. (Belum ada latihan untuk jenis ini.)</p>
                </div>
            @else
                <ul class="mt-4 space-y-2.5">
                    @foreach ($exercises as $exercise)
                        @php $t = $exercise->exercise_type->theme(); @endphp
                        <li class="flex flex-wrap items-center gap-3 rounded-2xl border border-navy-100 bg-white p-3 shadow-sm sm:flex-nowrap sm:p-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $t['icon'] }}"><x-icon :name="$exercise->exercise_type->icon()" /></span>

                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-navy-900">{{ $exercise->title }}</h3>
                                @if ($exercise->description)
                                    <p class="truncate text-sm text-navy-700">{{ $exercise->description }}</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-col gap-0.5 text-xs text-navy-700">
                                <span class="inline-flex items-center gap-1.5"><x-icon name="document" size="h-4 w-4" /> {{ $exercise->questions_count }} soal</span>
                                <span class="inline-flex w-fit rounded-full px-2 py-0.5 font-semibold {{ $t['soft'] }}">{{ str_replace('Latihan ', '', $exercise->exercise_type->label()) }}</span>
                            </div>

                            <a href="{{ route('exercises.show', $exercise) }}"
                               class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full bg-sibaya-navy-800 px-5 py-2 text-sm font-bold text-white transition hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2 sm:w-auto"
                               aria-label="Mulai {{ $exercise->title }}">
                                Mulai Latihan <x-icon name="arrow-right" size="h-4 w-4" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-4" aria-label="Pangeling">
            <figure class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-50 to-emerald-50 p-5 ring-1 ring-navy-100">
                <blockquote class="font-display text-lg italic leading-snug text-navy-800">“Saben latihan iku langkah cilik kanggo nggayuh pangerten sing luwih jedhe.”</blockquote>
            </figure>

            <section id="tips" class="rounded-2xl bg-navy-50 p-5 ring-1 ring-navy-100" aria-labelledby="tips-sinau">
                <h2 id="tips-sinau" class="flex items-center gap-2 font-display text-lg font-extrabold text-navy-800"><x-icon name="bulb" class="text-gold-600" /> Tips Sinau</h2>
                <ul class="mt-3 space-y-2 text-sm text-navy-900">
                    @foreach ([
                        'Waca maneh materi sing isih bingung.',
                        'Gunakake cathetan pribadi.',
                        'Aja wedi salah, amarga saka salah kowe sinau.',
                        'Tetep konsisten lan nikmati prosesé.',
                    ] as $tip)
                        <li class="flex gap-2"><x-icon name="check" size="mt-0.5 h-4 w-4 shrink-0 text-navy-600" /> {{ $tip }}</li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
</x-exercise.shell>
