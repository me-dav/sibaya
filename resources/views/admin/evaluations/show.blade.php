@php use App\Support\Labels; $fmtDt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d M Y H:i') : '-'; @endphp
<x-layouts.admin :title="'Kelola Soal: '.$evaluation->title">
    <x-breadcrumb :items="['Evaluasi' => route('admin.evaluations.index'), $evaluation->title => null]" />
    <div class="mt-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-sibaya-navy-800">{{ $evaluation->title }} <span class="text-base uppercase text-slate-500">({{ Labels::value($evaluation->type) }})</span></h1>
            <p class="mt-1 text-sm text-slate-600">
                {{ $evaluation->is_published ? 'Terbit' : 'Draft' }} &middot; {{ $evaluation->duration_minutes }} menit &middot; lulus &ge; {{ $evaluation->passing_score }}
                &middot; maks {{ $evaluation->max_attempts }}x &middot; {{ $questions->count() }} soal &middot; total {{ $questions->sum('points') }} poin<br>
                Dibuka: {{ $fmtDt($evaluation->start_at) }} &middot; Ditutup: {{ $fmtDt($evaluation->end_at) }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.evaluations.results', $evaluation) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Delengi Hasil</a>
            <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Ubah Evaluasi</a>
        </div>
    </div>
    <x-flash />

    @if (! $evaluation->is_published && $publishErrors)
        <div role="note" class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200">
            Durung bisa diterbitake: {{ implode(' ', $publishErrors) }}
        </div>
    @endif
    @if ($locked)
        <div role="note" class="mt-4 rounded-lg bg-sky-50 px-4 py-3 text-sm text-sky-900 ring-1 ring-sky-200">
            Wis ana {{ $attempts }} percobaan siswa, mula daftar soal dikunci (ora bisa nambah, nyopot, utawa nata ulang).
        </div>
    @endif

    <ol class="mt-6 space-y-3">
        @forelse ($questions as $q)
            <li class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Soal {{ $loop->iteration }} &middot; {{ $q->points }} poin &middot;
                            {{ $q->exercise_id ? 'Saka latihan: '.($exerciseTitle[$q->exercise_id] ?? '-') : 'Soal mandiri' }}
                        </p>
                        <p class="mt-1 font-medium">{{ \Illuminate\Support\Str::limit($q->question_text, 180) }}</p>
                        @if (Labels::value($q->question_type) === 'multiple_choice')
                            <p class="mt-1 text-sm text-slate-600">
                                @foreach ($q->options as $o){{ $o->option_key }}. {{ \Illuminate\Support\Str::limit($o->option_text, 40) }}@if($o->is_correct) (&#10003;)@endif{{ $loop->last ? '' : ' | ' }}@endforeach
                            </p>
                        @else
                            <p class="mt-1 text-sm text-slate-600">Jawaban: {{ $q->answer_key }}</p>
                        @endif
                    </div>
                    @unless ($locked)
                        <div class="flex items-center gap-1 text-sm">
                            <form method="POST" action="{{ route('admin.evaluations.questions.move', [$evaluation, $q]) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="up">
                                <button class="rounded border border-slate-300 px-2 py-1 disabled:opacity-40" @disabled($loop->first) aria-label="Munggahake soal {{ $loop->iteration }}">&uarr;</button></form>
                            <form method="POST" action="{{ route('admin.evaluations.questions.move', [$evaluation, $q]) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="down">
                                <button class="rounded border border-slate-300 px-2 py-1 disabled:opacity-40" @disabled($loop->last) aria-label="Medunake soal {{ $loop->iteration }}">&darr;</button></form>
                            @if ($q->exercise_id === null)
                                <a href="{{ route('admin.evaluations.questions.edit', [$evaluation, $q]) }}" class="ml-2 text-sibaya-navy-700 hover:underline">Ubah</a>
                            @else
                                <a href="{{ route('admin.questions.edit', $q) }}" class="ml-2 text-sibaya-navy-700 hover:underline">Ubah (latihan)</a>
                            @endif
                            <form method="POST" action="{{ route('admin.evaluations.questions.detach', [$evaluation, $q]) }}" data-confirm="Copot soal iki saka evaluasi?" onsubmit="return confirm(this.dataset.confirm)">@csrf @method('DELETE')
                                <button class="ml-2 text-red-700 hover:underline">Copot</button></form>
                        </div>
                    @else
                        @if ($q->exercise_id === null)<a href="{{ route('admin.evaluations.questions.edit', [$evaluation, $q]) }}" class="text-sm text-sibaya-navy-700 hover:underline">Ubah</a>@endif
                    @endunless
                </div>
            </li>
        @empty
            <li class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500">Durung ana soal. Gawe soal anyar utawa jupuk saka bank soal.</li>
        @endforelse
    </ol>

    @unless ($locked)
        <div class="mt-6 rounded-xl bg-sibaya-sky-100 p-4">
            <a href="{{ route('admin.evaluations.questions.create', $evaluation) }}" class="inline-block rounded-full bg-sibaya-navy-800 px-5 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">
                + Gawe soal {{ Labels::value($evaluation->type) === 'uts' ? 'pilihan ganda' : 'isian' }} anyar
            </a>
        </div>

        <section class="mt-8 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200" aria-labelledby="h-bank">
            <h2 id="h-bank" class="text-lg font-bold text-sibaya-navy-800">Jupuk saka bank soal</h2>
            <form method="GET" class="mt-3 flex items-end gap-3">
                <div><label for="bank_q" class="block text-sm font-medium">Golek teks soal</label>
                    <input id="bank_q" name="bank_q" value="{{ request('bank_q') }}" class="mt-1 rounded-lg border-slate-300 text-sm"></div>
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Golek</button>
            </form>

            <form method="POST" action="{{ route('admin.evaluations.questions.attach', $evaluation) }}" class="mt-4">
                @csrf
                <x-input-error :messages="$errors->get('question_ids')" class="mb-2" />
                <ul class="max-h-80 divide-y divide-slate-100 overflow-y-auto rounded-lg ring-1 ring-slate-200">
                    @forelse ($bank as $b)
                        <li class="flex items-start gap-3 px-4 py-2 text-sm">
                            <input type="checkbox" id="bank-{{ $b->id }}" name="question_ids[]" value="{{ $b->id }}" class="mt-1 rounded border-slate-300">
                            <label for="bank-{{ $b->id }}" class="flex-1">{{ \Illuminate\Support\Str::limit($b->question_text, 160) }} <span class="text-xs text-slate-500">({{ $b->points }} poin)</span></label>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-slate-500">Ora ana soal sing cocog ing bank soal.</li>
                    @endforelse
                </ul>
                @if ($bank->isNotEmpty())
                    <button class="mt-3 rounded-lg bg-sibaya-navy-800 px-5 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Tambahake soal sing dipilih</button>
                @endif
            </form>
        </section>
    @endunless
</x-layouts.admin>
