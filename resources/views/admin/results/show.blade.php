@php
    use App\Support\Labels;
    $fmt = fn ($n) => number_format((float) $n, 1, ',', '.');
@endphp
<x-layouts.admin title="Detail Hasil">
    <x-breadcrumb :items="['Hasil' => route('admin.results.index'), 'Detail' => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">{{ $attempt->exercise?->title }}</h1>

    <dl class="mt-4 grid gap-4 rounded-xl bg-white p-5 text-sm shadow-sm ring-1 ring-slate-200 sm:grid-cols-5">
        <div><dt class="text-slate-500">Siswa</dt><dd class="font-medium">{{ $attempt->user?->name }}<br><span class="text-xs text-slate-500">{{ $attempt->user?->class_name }}</span></dd></div>
        <div><dt class="text-slate-500">Nilai</dt><dd class="text-2xl font-extrabold text-sibaya-navy-800">{{ $fmt($attempt->score) }}</dd></div>
        <div><dt class="text-slate-500">Poin</dt><dd class="font-medium">{{ $attempt->total_points }}</dd></div>
        <div><dt class="text-slate-500">Bener / Salah</dt><dd class="font-medium">{{ $attempt->correct_answers }} / {{ $attempt->wrong_answers }}</dd></div>
        <div><dt class="text-slate-500">Status</dt><dd class="font-medium">{{ Labels::value($attempt->status) }}</dd></div>
    </dl>

    <ol class="mt-6 space-y-4">
        @foreach ($answers as $a)
            @php
                $q = $a->question; $type = Labels::value($q?->question_type);
                $correctOption = $q?->options?->firstWhere('is_correct', true);
            @endphp
            <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-medium">{{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($q?->question_text ?: $q?->passage_text, 220) }}</p>
                    <span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $a->is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $a->is_correct ? '✓ Bener' : '✗ Salah' }} &middot; {{ $a->points_earned }}/{{ $q?->points }}
                    </span>
                </div>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Jawaban siswa</dt>
                        <dd class="font-medium">
                            @if ($a->selectedOption) {{ $a->selectedOption->option_key }}. {{ $a->selectedOption->option_text }}
                            @elseif (filled($a->answer_text)) <span class="whitespace-pre-line">{{ $a->answer_text }}</span>
                            @else <em class="text-slate-500">(kosong)</em> @endif
                        </dd></div>
                    <div><dt class="text-slate-500">Kunci jawaban</dt>
                        <dd class="font-medium">
                            @if ($correctOption) {{ $correctOption->option_key }}. {{ $correctOption->option_text }}
                            @elseif ($type === 'text_analysis')
                                @foreach (data_get($q->metadata, 'analysis_rows', []) as $row){{ $row['label'] }}: {{ implode(', ', $row['keywords']) }}<br>@endforeach
                            @else {{ $q?->answer_key }} @endif
                        </dd></div>
                </dl>
            </li>
        @endforeach
    </ol>
</x-layouts.admin>
