@php use App\Support\Labels; $qLabels = Labels::questionTypes(); @endphp
<x-layouts.admin :title="'Kelola Soal: '.$exercise->title">
    <x-breadcrumb :items="['Gladhen' => route('admin.exercises.index'), $exercise->title => null]" />
    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-sibaya-navy-800">{{ $exercise->title }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                {{ Labels::exerciseTypes()[Labels::value($exercise->exercise_type)] ?? '' }} &middot;
                {{ $exercise->is_published ? 'Terbit' : 'Draft' }} &middot; {{ $questions->count() }} soal &middot; total {{ $questions->sum('points') }} poin
                @if ($exercise->is_randomized) &middot; soal diacak @endif
            </p>
        </div>
        <a href="{{ route('admin.exercises.edit', $exercise) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Ubah Latihan</a>
    </div>
    <x-flash />

    <div class="mt-6 flex flex-wrap items-center gap-2 rounded-xl bg-sibaya-sky-100 p-4">
        <span class="text-sm font-semibold text-sibaya-navy-800">Tambah soal:</span>
        @foreach ($allowedTypes as $t)
            <a href="{{ route('admin.exercises.questions.create', ['exercise' => $exercise, 'type' => $t]) }}"
               class="rounded-full bg-sibaya-navy-800 px-4 py-1.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">+ {{ $qLabels[$t] }}</a>
        @endforeach
    </div>

    <ol class="mt-6 space-y-4">
        @forelse ($questions as $q)
            @php $type = Labels::value($q->question_type); $used = in_array($q->id, $usedIds, true); @endphp
            <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Soal {{ $loop->iteration }} &middot; {{ $qLabels[$type] ?? $type }} &middot; {{ $q->points }} poin
                            @if ($used)&middot; <span class="text-amber-700">wis dijawab/dipakai</span>@endif</p>
                        <p class="mt-1 font-medium">{{ \Illuminate\Support\Str::limit($q->question_text ?: $q->passage_text, 200) }}</p>
                    </div>
                    <div class="flex items-center gap-1 text-sm">
                        <form method="POST" action="{{ route('admin.questions.move', $q) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="up">
                            <button class="rounded border border-slate-300 px-2 py-1 disabled:opacity-40" @disabled($loop->first) aria-label="Munggahake soal {{ $loop->iteration }}">&uarr;</button></form>
                        <form method="POST" action="{{ route('admin.questions.move', $q) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="down">
                            <button class="rounded border border-slate-300 px-2 py-1 disabled:opacity-40" @disabled($loop->last) aria-label="Medunake soal {{ $loop->iteration }}">&darr;</button></form>
                        <a href="{{ route('admin.questions.edit', $q) }}" class="ml-2 text-sibaya-navy-700 hover:underline">Ubah</a>
                        <form method="POST" action="{{ route('admin.questions.destroy', $q) }}" data-confirm="Busak soal iki?" onsubmit="return confirm(this.dataset.confirm)">@csrf @method('DELETE')
                            <button class="ml-2 text-red-700 hover:underline">Busak</button></form>
                    </div>
                </div>

                @if (in_array($type, ['multiple_choice', 'audio_choice'], true))
                    @if ($type === 'audio_choice' && $q->audio_path)
                        <audio controls controlsList="nodownload" preload="none" class="mt-3 w-full"><source src="{{ route('admin.questions.audio', $q) }}"></audio>
                    @endif
                    <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                        @foreach ($q->options as $o)
                            <li class="rounded-lg px-3 py-2 ring-1 {{ $o->is_correct ? 'bg-green-50 ring-green-300' : 'ring-slate-200' }}">
                                <strong>{{ $o->option_key }}.</strong> {{ $o->option_text }}@if ($o->is_correct) <span class="font-semibold text-green-800">(&#10003; kunci)</span>@endif
                            </li>
                        @endforeach
                    </ul>
                @elseif ($type === 'text_analysis')
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach (data_get($q->metadata, 'analysis_rows', []) as $row)
                            <li><strong>{{ $row['label'] }}</strong> ({{ $row['points'] }} poin): {{ implode(', ', $row['keywords']) }}</li>
                        @endforeach
                    </ul>
                @elseif ($type === 'short_answer')
                    <p class="mt-3 text-sm"><strong>Jawaban:</strong> {{ $q->answer_key }}
                        @php $kw = is_array($q->keywords) ? $q->keywords : (json_decode((string) $q->keywords, true) ?: []); @endphp
                        @if ($kw) <br><strong>Kata kunci:</strong> {{ implode(', ', $kw) }} @endif</p>
                @endif
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500">Durung ana soal. Pilih tipe soal ing ndhuwur kanggo miwiti.</li>
        @endforelse
    </ol>
</x-layouts.admin>
