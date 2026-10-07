<x-layouts.public title="Ruang Piwulang">
    <div class="mx-auto max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:px-8">

        <div class="lg:sticky lg:top-24 lg:self-start">
            <details class="mb-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200 lg:hidden">
                <summary class="cursor-pointer font-semibold text-sibaya-navy-800">Menu Ruang Piwulang</summary>
                <x-learning-sidebar class="mt-3 !p-0 !shadow-none !ring-0" />
            </details>
            <x-learning-sidebar class="hidden lg:block" />
        </div>

        <div class="min-w-0 space-y-10">
            <x-hero badge="Ruang Piwulang" title="Basa Rinengga" subtitle="Kelas X SMA">
                Sinau basa rinengga, ayo ngerteni pangertèn, jinis-jinis, lan tuladha panggonane ing teks lan urip saben dina.
                <p class="brush-gold mt-4 !text-xl">Mugi sinau nambahi kabecikan lan katrampilanmu!</p>
            </x-hero>

            {{-- Pambuka --}}
            <section id="pambuka" class="scroll-mt-24" aria-labelledby="h-pambuka">
                <h2 id="h-pambuka" class="mb-4 text-2xl font-bold text-sibaya-navy-800">Pambuka Basa Rinengga</h2>
                <div class="grid gap-6 rounded-2xl border border-sibaya-sky-200 bg-sibaya-sky-100 p-6 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h3 class="text-lg font-bold text-sibaya-navy-800">Apa iku Basa Rinengga?</h3>
                        <p class="mt-2 text-sm leading-relaxed">
                            Basa Rinengga yaiku basa kang dipaesi utawa direngga kanthi nggunakake tembung lan unen-unen tartamtu supaya basa dadi luwih endah, urip, lan nduweni teges kang luwih jero.
                        </p>
                    </div>
                    <div class="lg:col-span-4">
                        <h3 class="text-lg font-bold text-sibaya-navy-800">Tujuan Sinau</h3>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach (['Nerangake pangertèn Basa Rinengga', 'Ngenali jinis-jinis Basa Rinengga', 'Mbedakake ciri saben jinis', 'Nemokake Basa Rinengga ing teks', 'Nerangake teges adhedhasar konteks', 'Nggunakake Basa Rinengga kanthi trep'] as $goal)
                                <li class="flex gap-2"><span class="text-sibaya-gladhen-accent" aria-hidden="true">&#10003;</span>{{ $goal }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="flex items-center justify-center rounded-xl bg-white/60 p-4 lg:col-span-3">
                        @if ($shortcuts['audio'])
                            <a href="{{ route('learning.show', $shortcuts['audio']) }}#audio" class="inline-flex items-center gap-2 rounded-full bg-sibaya-navy-800 px-5 py-3 text-sm font-semibold text-white hover:bg-sibaya-navy-700">
                                <span aria-hidden="true">&#9654;</span> Rungokna Audio
                            </a>
                        @else
                            <span class="text-center text-sm text-sibaya-ink/60">Audio durung kasedhiya</span>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Enam jenis --}}
            <section id="jenis" class="scroll-mt-24" aria-labelledby="h-jenis">
                <h2 id="h-jenis" class="mb-4 text-2xl font-bold text-sibaya-navy-800">Jinis-Jinis Basa Rinengga</h2>
                @forelse ($types as $type)
                    @if ($loop->first)<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">@endif
                        <x-type-card :type="$type" :material="$type->materials->first()" />
                    @if ($loop->last)</div>@endif
                @empty
                    <p class="rounded-2xl border border-dashed border-sibaya-sky-200 p-8 text-center text-sibaya-ink/70">Durung ana jinis Basa Rinengga sing diterbitake.</p>
                @endforelse
            </section>

            {{-- Pintasan bagian materi --}}
            @php
                $cards = [
                    ['id' => 'ciri', 'key' => 'contoh', 'anchor' => '#contoh', 'title' => 'Ciri-Ciri lan Tuladha', 'desc' => 'Sinau ciri-ciri saben jinis lan tuladha ukara, tegese, lan bedane antarane jinis-jinis.', 'cta' => 'Waca Materi', 'box' => 'bg-blue-50 border-sibaya-sky-200'],
                    ['id' => 'teks', 'key' => 'teks', 'anchor' => '#isi', 'title' => 'Basa Rinengga ing Teks', 'desc' => 'Ngenali lan ngerteni teges Basa Rinengga adhedhasar konteks ing teks, crita, pacelathon, geguritan, lsp.', 'cta' => 'Waca Materi', 'box' => 'bg-purple-50 border-purple-200'],
                    ['id' => 'audio-nyimak', 'key' => 'audio', 'anchor' => '#audio', 'title' => 'Audio Nyimak', 'desc' => 'Rekaman teks, pacelathon, lan tuladha Basa Rinengga lengkap karo transkrip.', 'cta' => 'Rungokna Audio', 'box' => 'bg-emerald-50 border-emerald-200'],
                    ['id' => 'video-piwulang', 'key' => 'video', 'anchor' => '#video', 'title' => 'Video Piwulang', 'desc' => 'Video penjelasan materi, tuladha panggonane Basa Rinengga, lan panjelasan luwih jero.', 'cta' => 'Tonton Video', 'box' => 'bg-violet-50 border-violet-200'],
                    ['id' => 'rangkuman-materi', 'key' => 'rangkuman', 'anchor' => '#rangkuman', 'title' => 'Rangkuman Materi', 'desc' => 'Poin-poin penting, tabel ringkes, lan peta konsep Basa Rinengga.', 'cta' => 'Lihat Rangkuman', 'box' => 'bg-amber-50 border-amber-200'],
                ];
            @endphp
            <section class="grid gap-4 md:grid-cols-2" aria-label="Bagian materi">
                @foreach ($cards as $card)
                    @php $target = $shortcuts[$card['key']]; @endphp
                    <article id="{{ $card['id'] }}" class="flex scroll-mt-24 flex-col rounded-2xl border p-5 {{ $card['box'] }}">
                        <h3 class="text-lg font-bold text-sibaya-navy-800">{{ $card['title'] }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-sibaya-ink/80">{{ $card['desc'] }}</p>
                        @if ($target)
                            <a href="{{ route('learning.show', $target) }}{{ $card['anchor'] }}" class="mt-4 inline-flex w-fit items-center gap-2 rounded-full bg-sibaya-navy-800 px-5 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">
                                {{ $card['cta'] }} <span aria-hidden="true">&rarr;</span>
                            </a>
                        @else
                            <span class="mt-4 inline-flex w-fit rounded-full bg-white px-4 py-2 text-xs font-medium text-sibaya-ink/60 ring-1 ring-slate-200">Durung kasedhiya</span>
                        @endif
                    </article>
                @endforeach
            </section>
        </div>
    </div>
</x-layouts.public>
