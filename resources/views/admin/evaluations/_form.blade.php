@php
    use App\Support\EvaluationRules; use App\Support\Labels;
    $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700';
    $isEdit = $evaluation->exists;
    $dt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('Y-m-d\TH:i') : '';
@endphp
<form method="POST" action="{{ $action }}" class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any())<div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>@endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div><label for="title" class="block text-sm font-medium">Judul <span class="text-red-600">*</span></label>
            <input id="title" name="title" value="{{ old('title', $evaluation->title) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('title')" class="mt-1" /></div>
        <div><label for="type" class="block text-sm font-medium">Jenis evaluasi <span class="text-red-600">*</span></label>
            <select id="type" name="type" required class="{{ $input }}"><option value="">Pilih jenis</option>
                @foreach (EvaluationRules::types() as $v => $l)<option value="{{ $v }}" @selected(old('type', Labels::value($evaluation->type)) === $v)>{{ $l }} &ndash; {{ $v === 'uts' ? 'pilihan ganda' : 'isian' }}</option>@endforeach</select>
            <x-input-error :messages="$errors->get('type')" class="mt-1" /></div>
        <div><label for="duration_minutes" class="block text-sm font-medium">Durasi (menit) <span class="text-red-600">*</span></label>
            <input id="duration_minutes" type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $evaluation->duration_minutes) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('duration_minutes')" class="mt-1" /></div>
        <div><label for="passing_score" class="block text-sm font-medium">Nilai kelulusan (0-100) <span class="text-red-600">*</span></label>
            <input id="passing_score" type="number" min="0" max="100" name="passing_score" value="{{ old('passing_score', $evaluation->passing_score) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('passing_score')" class="mt-1" /></div>
        <div><label for="max_attempts" class="block text-sm font-medium">Maksimum percobaan <span class="text-red-600">*</span></label>
            <input id="max_attempts" type="number" min="1" max="20" name="max_attempts" value="{{ old('max_attempts', $evaluation->max_attempts) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('max_attempts')" class="mt-1" /></div>
        <div></div>
        <div><label for="start_at" class="block text-sm font-medium">Dibuka (opsional)</label>
            <input id="start_at" type="datetime-local" name="start_at" value="{{ old('start_at', $dt($evaluation->start_at)) }}" class="{{ $input }}"><x-input-error :messages="$errors->get('start_at')" class="mt-1" /></div>
        <div><label for="end_at" class="block text-sm font-medium">Ditutup (opsional)</label>
            <input id="end_at" type="datetime-local" name="end_at" value="{{ old('end_at', $dt($evaluation->end_at)) }}" aria-describedby="end-hint" class="{{ $input }}">
            <p id="end-hint" class="mt-1 text-xs text-slate-500">Batas untuk MEMULAI. Siswa yang sudah mulai tetap diatur durasinya.</p>
            <x-input-error :messages="$errors->get('end_at')" class="mt-1" /></div>
    </div>

    <div><label for="description" class="block text-sm font-medium">Deskripsi</label>
        <textarea id="description" name="description" rows="2" class="{{ $input }}">{{ old('description', $evaluation->description) }}</textarea><x-input-error :messages="$errors->get('description')" class="mt-1" /></div>
    <div><label for="instructions" class="block text-sm font-medium">Petunjuk pengerjaan</label>
        <textarea id="instructions" name="instructions" rows="4" class="{{ $input }}">{{ old('instructions', $evaluation->instructions) }}</textarea><x-input-error :messages="$errors->get('instructions')" class="mt-1" /></div>

    @if ($isEdit)
        <div><div class="flex items-center gap-2"><input type="hidden" name="is_published" value="0">
            <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $evaluation->is_published)) class="rounded border-slate-300">
            <label for="is_published" class="text-sm font-medium">Terbitake (katon kanggo siswa)</label></div>
            <x-input-error :messages="$errors->get('is_published')" class="mt-1" /></div>
    @else
        <p class="text-xs text-slate-500">Evaluasi anyar dadi Draft. Terbitake sawise soal ditambahake.</p>
    @endif

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.evaluations.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
