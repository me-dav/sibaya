@php
    $t = $exercise->exercise_type->theme();
    $letters = ['A', 'B', 'C', 'D'];
@endphp

<x-exercise.shell :title="$exercise->title" :active="$exercise->exercise_type">

    <header class="rounded-3xl border-2 p-5 sm:p-6 {{ $t['card'] }}">
        <p class="text-xs font-bold {{ $t['text'] }}">{{ $exercise->exercise_type->label() }}</p>
        <h1 class="font-display text-3xl font-extrabold text-navy-800">{{ $exercise->title }}</h1>
        @if ($exercise->instructions)
            <p class="mt-2 whitespace-pre-line text-sm text-navy-900">{{ $exercise->instructions }}</p>
        @endif
    </header>

    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-bold">Jawaban durung bisa dikirim:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('exercises.submit', $attempt) }}" class="mt-5"
          x-data="{
              total: {{ $questions->count() }}, answered: 0, submitting: false,
              count() {
                  this.answered = [...this.$el.querySelectorAll('[data-question]')].filter(q =>
                      q.querySelector('input[type=radio]:checked') ||
                      [...q.querySelectorAll('input[type=text], textarea')].some(i => i.value.trim() !== '')
                  ).length;
              }
          }"
          x-init="count()" @input="count()" @change="count()"
          @submit="if (answered < total && !confirm('Isih ana soal sing durung dijawab. Tetep kirim?')) { $event.preventDefault(); return; } submitting = true">
        @csrf

        <ol class="space-y-5">
            @foreach ($questions as $q)
                @php $type = $q->question_type->value; $name = 'answers['.$q->id.']'; @endphp
                <li data-question class="rounded-2xl border border-navy-100 bg-white p-5 shadow-sm">
                    <fieldset>
                        <legend class="flex w-full gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sm font-bold text-white" aria-hidden="true">{{ $loop->iteration }}</span>
                            <span class="font-bold text-navy-900">
                                <span class="sr-only">Soal nomer {{ $loop->iteration }}: </span>
                                {{ $q->question_text ?? ($type === 'text_analysis' ? 'Temokake jinis Basa Rinengga ing teks ing ngisor iki.' : 'Rungokna audio banjur wangsulana.') }}
                                <span class="ml-1 text-xs font-semibold text-navy-600">({{ $q->maxPoints() }} poin)</span>
                            </span>
                        </legend>

                        <div class="mt-4 space-y-3 sm:pl-11">
                            @switch($type)

                                @case('audio_choice')
                                    <x-audio-player :src="route('exercises.audio', [$attempt, $q])" label="Rungokna audio" />
                                    @break

                                @case('text_analysis')
                                    <blockquote class="whitespace-pre-line rounded-xl border-l-4 border-gold-500 bg-cream-100 p-4 text-navy-900">{{ $q->passage_text }}</blockquote>
                                    @break

                            @endswitch

                            @if (in_array($type, ['multiple_choice', 'audio_choice'], true))
                                <div class="space-y-2">
                                    @foreach ($q->options as $opt)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-navy-100 p-3 transition hover:bg-navy-50 has-[:checked]:border-navy-500 has-[:checked]:bg-navy-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-navy-500">
                                            <input type="radio" name="{{ $name }}" value="{{ $opt->id }}" class="mt-1 text-navy-800 focus:ring-navy-500"
                                                   @checked((string) old('answers.'.$q->id) === (string) $opt->id)>
                                            <span><span class="font-bold">{{ $opt->option_key }}.</span> {{ $opt->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($type === 'short_answer')
                                <label for="jawab-{{ $q->id }}" class="sr-only">Wangsulan soal nomer {{ $loop->iteration }}</label>
                                <textarea id="jawab-{{ $q->id }}" name="{{ $name }}" rows="3" maxlength="500" placeholder="Tulisen wangsulanmu ing kene"
                                          class="block w-full rounded-xl border-navy-200 shadow-sm focus:border-navy-500 focus:ring-2 focus:ring-gold-400">{{ old('answers.'.$q->id) }}</textarea>

                            @elseif ($type === 'text_analysis')
                                <div class="overflow-x-auto rounded-xl border border-navy-100">
                                    <table class="w-full min-w-[420px] text-left text-sm">
                                        <thead class="bg-navy-50 text-navy-800">
                                            <tr>
                                                <th scope="col" class="w-2/5 px-4 py-2.5">Jenis Basa Rinengga</th>
                                                <th scope="col" class="px-4 py-2.5">Wangsulan Siswa</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-navy-100">
                                            @foreach ($q->analysisLabels() as $i => $label)
                                                <tr>
                                                    <th scope="row" class="px-4 py-2 font-semibold text-navy-900">{{ $label }}</th>
                                                    <td class="px-4 py-2">
                                                        <label for="jawab-{{ $q->id }}-{{ $i }}" class="sr-only">Wangsulan kanggo {{ $label }}</label>
                                                        <input id="jawab-{{ $q->id }}-{{ $i }}" type="text" name="{{ $name }}[{{ $i }}]" maxlength="500"
                                                               value="{{ old('answers.'.$q->id.'.'.$i) }}"
                                                               class="block w-full rounded-lg border-navy-200 shadow-sm focus:border-navy-500 focus:ring-2 focus:ring-gold-400">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-xs text-navy-600">Wangsulan kena dikosongi.</p>
                            @endif
                        </div>
                    </fieldset>
                </li>
            @endforeach
        </ol>

        <div class="sticky bottom-0 mt-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-navy-100 bg-white/95 p-4 shadow-lg backdrop-blur">
            <p class="text-sm font-semibold text-navy-800" aria-live="polite">Wis dijawab: <span x-text="answered"></span> / <span x-text="total"></span></p>
            <x-primary-button x-bind:disabled="submitting">
                <span x-show="!submitting">Kirim</span>
                <span x-show="submitting" x-cloak>Ngirim...</span>
            </x-primary-button>
        </div>
    </form>
</x-exercise.shell>
