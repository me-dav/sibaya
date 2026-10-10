@php
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
    $statusLabel = ['completed' => 'Rampung', 'expired' => 'Wektu entek', 'in_progress' => 'Lagi dikerjakake', 'abandoned' => 'Ditinggal'];
@endphp
<x-layouts.public title="Dashboard">
    <div class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

        <header class="rounded-3xl bg-gradient-to-r from-sibaya-sky-200 via-white to-amber-50 p-6 shadow-sm ring-1 ring-sibaya-sky-200 sm:p-8">
            <p class="font-script text-2xl text-sibaya-navy-800">Sugeng rawuh,</p>
            <h1 class="text-3xl font-extrabold text-sibaya-navy-800">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-sibaya-ink/80">{{ $user->school_name }} &middot; Kelas {{ $user->class_name }}</p>
        </header>

        <x-flash />

        <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan nilai">
            @foreach (['UTS' => $uts, 'UAS' => $uas] as $label => $b)
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-sibaya-sky-200">
                    <p class="text-sm text-slate-600">Nilai {{ $label }} <span class="text-xs">(paling dhuwur)</span></p>
                    @if ($b)
                        <p class="mt-1 text-4xl font-extrabold text-sibaya-navy-800">{{ $fmt($b['score']) }}</p>
                        <p class="mt-1 text-sm font-semibold {{ $b['passed'] ? 'text-green-800' : 'text-red-800' }}">{{ $b['passed'] ? '✓ Lulus' : '✗ Durung lulus' }}</p>
                        <p class="text-xs text-slate-500">{{ $b['title'] }}</p>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Durung ana nilai. <a href="{{ route('evaluations.index') }}" class="text-sibaya-navy-700 underline">Menyang Evaluasi</a></p>
                    @endif
                </div>
            @endforeach
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-sibaya-sky-200">
                <p class="text-sm text-slate-600">Gladhen pungkasan</p>
                @if ($lastExercise)
                    <p class="mt-1 text-4xl font-extrabold text-sibaya-navy-800">{{ $fmt($lastExercise->score) }}</p>
                    <p class="text-sm">{{ $exerciseTitles[$lastExercise->exercise_id] ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $exerciseTotal }} percobaan rampung</p>
                @else
                    <p class="mt-2 text-sm text-slate-500">Durung ana latihan. <a href="{{ Route::has('exercises.index') ? route('exercises.index') : '#' }}" class="text-sibaya-navy-700 underline">Miwiti Gladhen</a></p>
                @endif
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-sertifikat">
            <h2 id="h-sertifikat" class="text-xl font-bold text-sibaya-navy-800">Sertifikat</h2>
            <div class="mt-3"><x-certificate-list :items="$certificates" /></div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-rekom">
            <h2 id="h-rekom" class="text-xl font-bold text-sibaya-navy-800">Rekomendasi Materi
                @if ($recommendations['unread'] > 0)<span class="ml-2 rounded-full bg-sibaya-navy-800 px-2.5 py-0.5 text-xs font-semibold text-white">{{ $recommendations['unread'] }} anyar</span>@endif</h2>
            <div class="mt-3"><x-recommendation-list :summary="$recommendations" /></div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-riwayat">
                <h2 id="h-riwayat" class="text-xl font-bold text-sibaya-navy-800">Riwayat Evaluasi</h2>
                <ul class="mt-3 divide-y divide-slate-100 text-sm">
                    @forelse ($history as $a)
                        @php
                            $ev = $evaluations->get($a->evaluation_id);
                            $st = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status;
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span>{{ $ev?->title }}<br><span class="text-xs text-slate-500">{{ $a->started_at?->translatedFormat('d M Y H:i') }} &middot; {{ $statusLabel[$st] ?? $st }}</span></span>
                            @if ($st === 'in_progress')
                                <a href="{{ route('evaluations.attempts.show', $a) }}" class="text-sibaya-navy-700 underline">Lanjutake</a>
                            @else
                                <a href="{{ route('evaluations.attempts.result', $a) }}" class="font-semibold text-sibaya-navy-700 underline">{{ $fmt($a->score) }}
                                    {{ $a->score >= ($ev?->passing_score ?? 0) ? '✓' : '✗' }}</a>
                            @endif
                        </li>
                    @empty
                        <li class="py-4 text-slate-500">Durung ana riwayat evaluasi.</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-gladhen">
                <h2 id="h-gladhen" class="text-xl font-bold text-sibaya-navy-800">Hasil Gladhen</h2>
                <ul class="mt-3 divide-y divide-slate-100 text-sm">
                    @forelse ($exerciseAttempts as $a)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-2">
                            <span>{{ $exerciseTitles[$a->exercise_id] ?? '-' }}<br>
                                <span class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($a->completed_at)->translatedFormat('d M Y H:i') }} &middot; bener {{ $a->correct_answers }}, salah {{ $a->wrong_answers }}</span></span>
                            <span class="font-semibold">{{ $fmt($a->score) }}</span>
                        </li>
                    @empty
                        <li class="py-4 text-slate-500">Durung ana hasil gladhen.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-layouts.public>
