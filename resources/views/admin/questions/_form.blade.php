@php
    use App\Support\Labels;
    $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700';
    $isEdit = $question->exists;

    $optionText = fn ($k) => optional($question->options?->firstWhere('option_key', $k))->option_text;
    $correct = old('correct_option', $isEdit ? optional($question->options->firstWhere('is_correct', true))->option_key : null);

    $stored = collect(data_get($question->metadata, 'analysis_rows', []))
        ->map(fn ($r) => ['label' => $r['label'], 'keywords' => implode(', ', $r['keywords'] ?? []), 'points' => $r['points']])->all();
    $rows = old('analysis_rows', $stored ?: [['label' => '', 'keywords' => '', 'points' => 5]]);
    $rows = array_values($rows);

    $kw = is_array($question->keywords) ? $question->keywords : (json_decode((string) $question->keywords, true) ?: []);
    $analysisErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'analysis_rows'))->flatMap(fn ($k) => $errors->get($k))->unique();
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ type: @js($type), rows: @js($rows) }" class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any())<div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>@endif

    {{-- Tipe soal --}}
    @if ($isEdit)
        <p class="text-sm"><span class="text-slate-500">Tipe soal:</span> <strong>{{ Labels::questionTypes()[$type] ?? $type }}</strong> <span class="text-xs text-slate-500">(ora bisa diowahi sawise digawe)</span></p>
    @else
        <div><label for="question_type" class="block text-sm font-medium">Tipe soal <span class="text-red-600">*</span></label>
            <select id="question_type" name="question_type" x-model="type" class="{{ $input }}">
                @foreach ($allowedTypes as $t)<option value="{{ $t }}">{{ Labels::questionTypes()[$t] }}</option>@endforeach
            </select><x-input-error :messages="$errors->get('question_type')" class="mt-1" /></div>
    @endif

    {{-- Teks yang dianalisis --}}
    <div x-show="type === 'text_analysis'" x-cloak>
        <label for="passage_text" class="block text-sm font-medium">Teks yang dianalisis <span class="text-red-600">*</span></label>
        <textarea id="passage_text" name="passage_text" rows="6" class="{{ $input }}">{{ old('passage_text', $question->passage_text) }}</textarea>
        <x-input-error :messages="$errors->get('passage_text')" class="mt-1" />
    </div>

    {{-- Pertanyaan --}}
    <div>
        <label for="question_text" class="block text-sm font-medium">
            <span x-text="type === 'text_analysis' ? 'Instruksi (opsional)' : 'Pertanyaan *'"></span></label>
        <textarea id="question_text" name="question_text" rows="3" class="{{ $input }}">{{ old('question_text', $question->question_text) }}</textarea>
        <x-input-error :messages="$errors->get('question_text')" class="mt-1" />
    </div>

    {{-- Audio --}}
    <div x-show="type === 'audio_choice'" x-cloak>
        <label for="audio_file" class="block text-sm font-medium">File audio (mp3, wav, ogg; maks 10 MB) @unless($isEdit && $question->audio_path)<span class="text-red-600">*</span>@endunless</label>
        <input id="audio_file" type="file" name="audio_file" accept="audio/mpeg,audio/wav,audio/ogg" class="mt-1 block w-full text-sm">
        @if ($isEdit && $question->audio_path)
            <audio controls controlsList="nodownload" class="mt-2 w-full"><source src="{{ route('admin.questions.audio', $question) }}"></audio>
            <p class="mt-1 text-xs text-slate-500">Pilih file anyar kanggo ngganti audio.</p>
        @endif
        <x-input-error :messages="$errors->get('audio_file')" class="mt-1" />
    </div>

    {{-- Pilihan ganda --}}
    <fieldset x-show="type === 'multiple_choice' || type === 'audio_choice'" x-cloak class="space-y-3">
        <legend class="text-sm font-medium">Opsi jawaban (A dan B wajib, pilih satu kunci)</legend>
        @foreach (['A', 'B', 'C', 'D'] as $k)
            @php $f = 'option_'.strtolower($k); @endphp
            <div class="flex items-start gap-3">
                <input type="radio" id="correct_{{ $k }}" name="correct_option" value="{{ $k }}" @checked($correct === $k) class="mt-3" aria-label="Jadikan opsi {{ $k }} minangka kunci">
                <div class="flex-1">
                    <label for="{{ $f }}" class="block text-sm">Opsi {{ $k }}@if (in_array($k, ['A', 'B'])) <span class="text-red-600">*</span>@endif</label>
                    <input id="{{ $f }}" name="{{ $f }}" value="{{ old($f, $optionText($k)) }}" class="{{ $input }}">
                    <x-input-error :messages="$errors->get($f)" class="mt-1" />
                </div>
            </div>
        @endforeach
        <x-input-error :messages="$errors->get('correct_option')" class="mt-1" />
    </fieldset>

    {{-- Analisis teks --}}
    <fieldset x-show="type === 'text_analysis'" x-cloak class="space-y-3">
        <legend class="text-sm font-medium">Baris analisis (siswa mengisi jawaban untuk tiap label)</legend>
        <template x-for="(row, i) in rows" :key="i">
            <div class="grid gap-3 rounded-lg bg-slate-50 p-3 sm:grid-cols-12">
                <div class="sm:col-span-3"><label class="block text-xs font-medium" :for="'row-label-'+i">Label (mis. Saloka)</label>
                    <input :id="'row-label-'+i" :name="`analysis_rows[${i}][label]`" x-model="row.label" class="{{ $input }}"></div>
                <div class="sm:col-span-6"><label class="block text-xs font-medium" :for="'row-kw-'+i">Kata kunci (pisahkan koma)</label>
                    <input :id="'row-kw-'+i" :name="`analysis_rows[${i}][keywords]`" x-model="row.keywords" class="{{ $input }}"></div>
                <div class="sm:col-span-2"><label class="block text-xs font-medium" :for="'row-pt-'+i">Poin</label>
                    <input :id="'row-pt-'+i" type="number" min="1" :name="`analysis_rows[${i}][points]`" x-model="row.points" class="{{ $input }}"></div>
                <div class="flex items-end sm:col-span-1"><button type="button" @click="rows.splice(i, 1)" x-show="rows.length > 1" class="text-sm text-red-700 hover:underline" :aria-label="'Busak baris ' + (i + 1)">Busak</button></div>
            </div>
        </template>
        <button type="button" @click="rows.push({label: '', keywords: '', points: 5})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold">+ Tambah baris</button>
        @foreach ($analysisErrors as $msg)<p class="text-sm text-red-600">{{ $msg }}</p>@endforeach
    </fieldset>

    {{-- Isian singkat --}}
    <div x-show="type === 'short_answer'" x-cloak class="grid gap-6 sm:grid-cols-2">
        <div><label for="answer_key" class="block text-sm font-medium">Jawaban bener <span class="text-red-600">*</span></label>
            <textarea id="answer_key" name="answer_key" rows="3" class="{{ $input }}">{{ old('answer_key', $question->answer_key) }}</textarea>
            <x-input-error :messages="$errors->get('answer_key')" class="mt-1" /></div>
        <div><label for="keywords" class="block text-sm font-medium">Kata kunci (pisahkan koma/baris)</label>
            <textarea id="keywords" name="keywords" rows="3" aria-describedby="kw-hint" class="{{ $input }}">{{ old('keywords', implode(', ', $kw)) }}</textarea>
            <p id="kw-hint" class="mt-1 text-xs text-slate-500">Jawaban siswa bener yen ngandhut salah siji kata kunci.</p>
            <x-input-error :messages="$errors->get('keywords')" class="mt-1" /></div>
    </div>

    {{-- Bobot --}}
    <div x-show="type !== 'text_analysis'" x-cloak class="max-w-xs">
        <label for="points" class="block text-sm font-medium">Bobot soal <span class="text-red-600">*</span></label>
        <input id="points" type="number" min="1" name="points" value="{{ old('points', $question->points ?? 1) }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('points')" class="mt-1" />
    </div>
    <p x-show="type === 'text_analysis'" x-cloak class="text-sm text-slate-600">Bobot total soal otomatis = jumlah poin saben baris.</p>

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.exercises.show', $exercise) }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
