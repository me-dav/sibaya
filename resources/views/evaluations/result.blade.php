@php
    use App\Support\Labels;
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
    $passed = $attempt->score >= $evaluation->passing_score;
    $dur = $attempt->duration_seconds !== null ? gmdate('i:s', (int) $attempt->duration_seconds) : '-';
@endphp
<x-layouts.public title="Hasil {{ $evaluation->title }}">
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <x-breadcrumb :items="['Evaluasi' => route('evaluations.index'), $evaluation->title => route('evaluations.show', $evaluation), 'Hasil' => null]" />

        <section class="mt-6 rounded-3xl bg-white p-8 text-center shadow-sm ring-1 ring-sibaya-sky-200" aria-labelledby="h-hasil">
            <h1 id="h-hasil" class="text-lg font-semibold text-sibaya-navy-800">Hasil {{ $evaluation->title }}</h1>
            @if ($attempt->status === 'expired')
                <p class="mt-2 inline-block rounded-full bg-amber-100 px-4 py-1 text-sm text-amber-900">⏱ Wektu entek, jawaban sing wis kasimpen dikirim otomatis.</p>
            @endif
            <p class="mt-4 text-6xl font-extrabold text-sibaya-navy-800">{{ $fmt($attempt->score) }}</p>
            <p class="mt-3 inline-block rounded-full px-5 py-1.5 text-sm font-bold {{ $passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $passed ? '✓ Lulus' : '✗ Durung lulus' }} <span class="font-normal">(minimal {{ $evaluation->passing_score }})</span>
            </p>
            <dl class="mt-6 grid grid-cols-3 gap-4 text-sm">
                <div><dt class="text-slate-500">Bener</dt><dd class="text-xl font-bold">{{ $attempt->correct_answers }}</dd></div>
                <div><dt class="text-slate-500">Salah / kosong</dt><dd class="text-xl font-bold">{{ $attempt->wrong_answers }}</dd></div>
                <div><dt class="text-slate-500">Wektu</dt><dd class="text-xl font-bold">{{ $dur }}</dd></div>
            </dl>
        </section>

        <section class="mt-8" aria-labelledby="h-rinci">
            <h2 id="h-rinci" class="text-xl font-bold text-sibaya-navy-800">Rincian Jawaban</h2>
            <p class="mt-1 text-sm text-slate-600">Kunci jawaban ora ditampilake.</p>
            <ol class="mt-4 space-y-3">
                @foreach ($questions as $q)
                    @php $a = $answers->get($q->id); $mc = Labels::value($q->question_type) === 'multiple_choice'; @endphp
                    <li class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium">{{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($q->question_text, 200) }}</p>
                            <span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $a?->is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $a?->is_correct ? '✓ Bener' : '✗ Salah' }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-700">Jawabanmu:
                            @if ($mc && $a?->selected_option_id) {{ optional($q->options->firstWhere('id', $a->selected_option_id))->option_key }}. {{ optional($q->options->firstWhere('id', $a->selected_option_id))->option_text }}
                            @elseif (! $mc && filled($a?->answer_text)) {{ $a->answer_text }}
                            @else <em>(kosong)</em> @endif
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="mt-8" aria-labelledby="h-sertifikat">
            <h2 id="h-sertifikat" class="text-xl font-bold text-sibaya-navy-800">Sertifikat</h2>
            <div class="mt-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200">
                @if ($certificate['certificate'])
                    <a href="{{ route('certificates.show', $certificate['certificate']) }}" class="inline-block rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Delengi / Cetak Sertifikat</a>
                @elseif ($certificate['can'])
                    <form method="POST" action="{{ route('certificates.issue', $attempt) }}">
                        @csrf
                        <button class="rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Terbitake Sertifikat</button>
                    </form>
                @else
                    <p class="text-sm text-slate-700" role="status">{{ $certificate['reason'] }}</p>
                @endif
            </div>
        </section>

        <section class="mt-8" aria-labelledby="h-rekom">
            <h2 id="h-rekom" class="text-xl font-bold text-sibaya-navy-800">Rekomendasi Materi</h2>
            <div class="mt-3"><x-recommendation-list :summary="$recommendations" /></div>
        </section>

        <div class="mt-8 flex gap-3">
            <a href="{{ route('evaluations.show', $evaluation) }}" class="rounded-full border border-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-sibaya-navy-800 hover:bg-sibaya-sky-100">Bali menyang evaluasi</a>
            <a href="{{ route('evaluations.index') }}" class="rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Kabeh evaluasi</a>
        </div>
    </div>
</x-layouts.public>
