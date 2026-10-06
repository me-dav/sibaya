@php
    $t = $exercise->exercise_type->theme();
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    $score = (float) $attempt->score;
    $message = $score >= 85 ? 'Apik tenan! Terusna sinau.' : ($score >= 60 ? 'Lumayan! Baleni materi supaya luwih mantep.' : 'Ayo baleni materi lan nyoba maneh.');
    $earnedTotal = $answers->sum(fn ($a) => (float) $a->points_earned);
@endphp

<x-exercise.shell :title="'Hasil '.$exercise->title" :active="$exercise->exercise_type">

    <section class="rounded-3xl border-2 p-6 sm:p-8 {{ $t['card'] }}" aria-labelledby="judul-hasil">
        <p class="text-xs font-bold {{ $t['text'] }}">{{ $exercise->exercise_type->label() }}</p>
        <h1 id="judul-hasil" class="font-display text-3xl font-extrabold text-navy-800">Hasil: {{ $exercise->title }}</h1>

        <div class="mt-5 grid gap-4 sm:grid-cols-[200px_1fr] sm:items-center">
            <div class="rounded-2xl bg-white p-5 text-center shadow-sm ring-1 ring-navy-100">
                <p class="text-xs font-semibold text-navy-600">Nilai</p>
                <p class="font-display text-5xl font-extrabold text-navy-800">{{ $fmt($score) }}</p>
                <p class="text-xs text-navy-600">{{ $fmt($earnedTotal) }} saka {{ $attempt->total_points }} poin</p>
            </div>

            <div>
                <p class="font-display text-xl font-bold text-navy-800">{{ $message }}</p>
                <dl class="mt-3 flex flex-wrap gap-3 text-sm">
                    <div class="flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-1.5 font-bold text-emerald-900">
                        <x-icon name="check" size="h-4 w-4" /> <dt>Bener</dt> <dd>{{ $attempt->correct_answers }}</dd>
                    </div>
                    <div class="flex items-center gap-2 rounded-full bg-rose-100 px-4 py-1.5 font-bold text-rose-900">
                        <x-icon name="x" size="h-4 w-4" /> <dt>Salah</dt> <dd>{{ $attempt->wrong_answers }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($exercise->is_published)
                        <form method="POST" action="{{ route('exercises.start', $exercise) }}">
                            @csrf
                            <x-primary-button><x-icon name="repeat" size="h-4 w-4" /> Baleni Latihan</x-primary-button>
                        </form>
                    @endif
                    <a href="{{ route('exercises.index') }}" class="inline-flex items-center gap-2 rounded-full border-2 border-navy-800 px-5 py-2 text-sm font-bold text-navy-800 hover:bg-navy-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
                        <x-icon name="arrow-left" size="h-4 w-4" /> Bali menyang daftar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="tinjauan">
        <h2 id="tinjauan" class="font-display text-2xl font-extrabold text-navy-800">Tinjauan Wangsulan</h2>

        <ol class="mt-3 space-y-4">
            @foreach ($answers as $a)
                @php
                    $q = $a->question;
                    $type = $q->question_type->value;
                    $earned = (float) $a->points_earned;
                    $state = $a->is_correct ? 'bener' : ($earned > 0 ? 'sebagian' : 'salah');
                    $badge = [
                        'bener' => ['Bener', 'check', 'bg-emerald-100 text-emerald-900'],
                        'sebagian' => ['Sebagian bener', 'minus', 'bg-amber-100 text-amber-900'],
                        'salah' => ['Salah', 'x', 'bg-rose-100 text-rose-900'],
                    ][$state];
                @endphp
                <li class="rounded-2xl border border-navy-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <p class="flex gap-3 font-bold text-navy-900">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sm text-white" aria-hidden="true">{{ $loop->iteration }}</span>
                            <span><span class="sr-only">Soal nomer {{ $loop->iteration }}: </span>{{ $q->question_text ?? ($type === 'text_analysis' ? 'Temokake jinis Basa Rinengga ing teks.' : 'Soal audio') }}</span>
                        </p>
                        <p class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-bold {{ $badge[2] }}">
                            <x-icon :name="$badge[1]" size="h-4 w-4" /> {{ $badge[0] }}
                            <span class="font-semibold">({{ $fmt($earned) }}/{{ $q->maxPoints() }})</span>
                        </p>
                    </div>

                    <div class="mt-3 space-y-2 sm:pl-11">
                        @if ($type === 'audio_choice')
                            <x-audio-player :src="route('exercises.audio', [$attempt, $q])" label="Audio soal" />
                        @endif

                        {{-- Pilihan ganda / audio --}}
                        @if (in_array($type, ['multiple_choice', 'audio_choice'], true))
                            @if (! $a->selected_option_id)
                                <p class="text-sm font-semibold text-rose-800">Ora dijawab.</p>
                            @endif
                            @foreach ($q->options as $opt)
                                @php
                                    $isChosen = $a->selected_option_id === $opt->id;
                                    $isKey = (bool) $opt->is_correct;
                                @endphp
                                <div class="flex flex-wrap items-center gap-2 rounded-xl border p-3 text-sm
                                    {{ $isKey ? 'border-emerald-300 bg-emerald-50' : ($isChosen ? 'border-rose-300 bg-rose-50' : 'border-navy-100') }}">
                                    <span><span class="font-bold">{{ $opt->option_key }}.</span> {{ $opt->option_text }}</span>
                                    @if ($isKey)
                                        <span class="ml-auto inline-flex items-center gap-1 font-bold text-emerald-900"><x-icon name="check" size="h-4 w-4" /> Wangsulan bener</span>
                                    @endif
                                    @if ($isChosen)
                                        <span class="{{ $isKey ? '' : 'ml-auto' }} inline-flex items-center gap-1 font-bold {{ $isKey ? 'text-emerald-900' : 'text-rose-900' }}">
                                            @unless ($isKey)<x-icon name="x" size="h-4 w-4" />@endunless Wangsulanmu
                                        </span>
                                    @endif
                                </div>
                            @endforeach

                        {{-- Isian singkat --}}
                        @elseif ($type === 'short_answer')
                            <dl class="space-y-2 text-sm">
                                <div class="rounded-xl border p-3 {{ $a->is_correct ? 'border-emerald-300 bg-emerald-50' : 'border-rose-300 bg-rose-50' }}">
                                    <dt class="font-bold">Wangsulanmu</dt>
                                    <dd class="mt-0.5 whitespace-pre-line">{{ $a->answer_text ?: 'Ora dijawab.' }}</dd>
                                </div>
                                @unless ($a->is_correct)
                                    <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-3">
                                        <dt class="font-bold text-emerald-900">Tuladha wangsulan bener</dt>
                                        <dd class="mt-0.5">{{ $q->answer_key }}</dd>
                                    </div>
                                @endunless
                            </dl>

                        {{-- Analisis teks --}}
                        @elseif ($type === 'text_analysis')
                            <blockquote class="whitespace-pre-line rounded-xl border-l-4 border-gold-500 bg-cream-100 p-3 text-sm">{{ $q->passage_text }}</blockquote>
                            <div class="overflow-x-auto rounded-xl border border-navy-100">
                                <table class="w-full min-w-[480px] text-left text-sm">
                                    <thead class="bg-navy-50 text-navy-800">
                                        <tr>
                                            <th scope="col" class="px-3 py-2">Jenis</th>
                                            <th scope="col" class="px-3 py-2">Wangsulanmu</th>
                                            <th scope="col" class="px-3 py-2">Hasil</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-navy-100">
                                        @foreach ($evaluator->analysisBreakdown($q, $a->answer_text) as $row)
                                            <tr>
                                                <th scope="row" class="px-3 py-2 font-semibold">{{ $row['label'] }}</th>
                                                <td class="px-3 py-2">{{ $row['answer'] !== '' ? $row['answer'] : 'Ora dijawab.' }}</td>
                                                <td class="px-3 py-2">
                                                    @if ($row['is_correct'])
                                                        <span class="inline-flex items-center gap-1 font-bold text-emerald-900"><x-icon name="check" size="h-4 w-4" /> Bener ({{ $row['points'] }}/{{ $row['max_points'] }})</span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 font-bold text-rose-900"><x-icon name="x" size="h-4 w-4" /> Salah (0/{{ $row['max_points'] }})</span>
                                                        @if ($row['expected'])<span class="block text-xs text-navy-700">Tuladha bener: {{ $row['expected'] }}</span>@endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
</x-exercise.shell>
