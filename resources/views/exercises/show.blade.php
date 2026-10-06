@php
    $t = $exercise->exercise_type->theme();
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $running = $history->firstWhere('status', \App\Enums\AttemptStatus::IN_PROGRESS);
    $statusLabel = [
        'in_progress' => 'Durung rampung',
        'completed' => 'Rampung',
        'abandoned' => 'Ditinggal',
    ];
@endphp

<x-exercise.shell :title="$exercise->title" :active="$exercise->exercise_type">

    <a href="{{ route('exercises.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-navy-700 hover:text-navy-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500">
        <x-icon name="arrow-left" size="h-4 w-4" /> Bali menyang daftar latihan
    </a>

    <section class="mt-4 rounded-3xl border-2 p-6 shadow-sm sm:p-8 {{ $t['card'] }}" aria-labelledby="judul-latihan">
        <div class="flex flex-wrap items-start gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $t['icon'] }}"><x-icon :name="$exercise->exercise_type->icon()" size="h-7 w-7" /></span>
            <div class="min-w-0 flex-1">
                <span class="inline-block rounded-full px-3 py-0.5 text-xs font-bold {{ $t['soft'] }}">{{ $exercise->exercise_type->label() }}</span>
                <h1 id="judul-latihan" class="mt-1 font-display text-3xl font-extrabold text-navy-800">{{ $exercise->title }}</h1>
                @if ($exercise->description)
                    <p class="mt-1 text-navy-900">{{ $exercise->description }}</p>
                @endif
            </div>
        </div>

        <dl class="mt-5 flex flex-wrap gap-x-8 gap-y-2 text-sm text-navy-900">
            <div><dt class="inline font-semibold">Cacahing soal:</dt> <dd class="inline">{{ $exercise->questions_count }}</dd></div>
            @if ($exercise->rinenggaType)
                <div><dt class="inline font-semibold">Materi:</dt> <dd class="inline">{{ $exercise->rinenggaType->name }}</dd></div>
            @endif
            <div><dt class="inline font-semibold">Wektu:</dt> <dd class="inline">Tanpa batas, bisa diulang</dd></div>
        </dl>

        @if ($exercise->instructions)
            <div class="mt-5 rounded-2xl bg-white/80 p-4">
                <h2 class="font-bold text-navy-800">Pandhuan</h2>
                <p class="mt-1 whitespace-pre-line text-sm text-navy-900">{{ $exercise->instructions }}</p>
            </div>
        @endif

        <div class="mt-6">
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-navy-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
                    Mlebu kanggo miwiti <x-icon name="arrow-right" size="h-4 w-4" />
                </a>
            @else
                @if ($exercise->questions_count > 0)
                    <form method="POST" action="{{ route('exercises.start', $exercise) }}">
                        @csrf
                        <x-primary-button>
                            @if ($running) Terusake Latihan @else Wiwiti Latihan @endif
                            <x-icon name="arrow-right" size="h-4 w-4" />
                        </x-primary-button>
                    </form>
                @else
                    <p class="rounded-xl bg-white/80 px-4 py-2 text-sm font-semibold text-navy-800">Latihan iki durung duwe soal.</p>
                @endif
            @endguest
        </div>
    </section>

    @auth
        <section class="mt-8" aria-labelledby="riwayat">
            <h2 id="riwayat" class="font-display text-2xl font-extrabold text-navy-800">Riwayat Percobaan</h2>

            @if ($history->isEmpty())
                <p class="mt-2 rounded-2xl border border-dashed border-navy-200 bg-white p-6 text-center text-sm text-navy-700">Durung ana percobaan. Ayo miwiti!</p>
            @else
                <div class="mt-3 overflow-x-auto rounded-2xl border border-navy-100 bg-white">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <caption class="sr-only">Riwayat percobaan {{ $exercise->title }}</caption>
                        <thead class="bg-navy-50 text-navy-800">
                            <tr>
                                <th scope="col" class="px-4 py-2.5">Tanggal</th>
                                <th scope="col" class="px-4 py-2.5">Nilai</th>
                                <th scope="col" class="px-4 py-2.5">Bener / Salah</th>
                                <th scope="col" class="px-4 py-2.5">Status</th>
                                <th scope="col" class="px-4 py-2.5"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy-100">
                            @foreach ($history as $a)
                                @php $isDone = $a->status === \App\Enums\AttemptStatus::COMPLETED; @endphp
                                <tr>
                                    <td class="px-4 py-2.5">{{ $a->started_at->timezone(config('app.timezone'))->format('d-m-Y H:i') }}</td>
                                    <td class="px-4 py-2.5 font-bold">{{ $isDone ? $fmt($a->score) : '-' }}</td>
                                    <td class="px-4 py-2.5">{{ $isDone ? $a->correct_answers.' / '.$a->wrong_answers : '-' }}</td>
                                    <td class="px-4 py-2.5">{{ $statusLabel[$a->status->value] }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a class="font-bold text-navy-700 underline-offset-2 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500"
                                           href="{{ $isDone ? route('exercises.result', $a) : route('exercises.attempt', $a) }}">{{ $isDone ? 'Deleng hasil' : 'Terusake' }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endauth
</x-exercise.shell>
