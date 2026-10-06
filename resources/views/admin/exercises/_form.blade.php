@php use App\Support\Labels; $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700'; $isEdit = $exercise->exists; @endphp
<form method="POST" action="{{ $action }}" class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any())<div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>@endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div><label for="title" class="block text-sm font-medium">Judul <span class="text-red-600">*</span></label>
            <input id="title" name="title" value="{{ old('title', $exercise->title) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('title')" class="mt-1" /></div>
        <div><label for="slug" class="block text-sm font-medium">Slug (URL)</label>
            <input id="slug" name="slug" value="{{ old('slug', $exercise->slug) }}" aria-describedby="slug-hint" class="{{ $input }}">
            <p id="slug-hint" class="mt-1 text-xs text-slate-500">Kosongkan untuk dibuat otomatis.</p><x-input-error :messages="$errors->get('slug')" class="mt-1" /></div>
        <div><label for="exercise_type" class="block text-sm font-medium">Jenis latihan <span class="text-red-600">*</span></label>
            <select id="exercise_type" name="exercise_type" required class="{{ $input }}"><option value="">Pilih jenis</option>
                @foreach (Labels::exerciseTypes() as $v => $l)<option value="{{ $v }}" @selected(old('exercise_type', Labels::value($exercise->exercise_type)) === $v)>{{ $l }}</option>@endforeach</select>
            <x-input-error :messages="$errors->get('exercise_type')" class="mt-1" /></div>
        <div><label for="rinengga_type_id" class="block text-sm font-medium">Jenis Basa Rinengga (opsional)</label>
            <select id="rinengga_type_id" name="rinengga_type_id" class="{{ $input }}"><option value="">Kabeh / campuran</option>
                @foreach ($types as $t)<option value="{{ $t->id }}" @selected(old('rinengga_type_id', $exercise->rinengga_type_id) == $t->id)>{{ $t->name }}</option>@endforeach</select>
            <x-input-error :messages="$errors->get('rinengga_type_id')" class="mt-1" /></div>
    </div>

    <div><label for="description" class="block text-sm font-medium">Deskripsi</label>
        <textarea id="description" name="description" rows="2" class="{{ $input }}">{{ old('description', $exercise->description) }}</textarea><x-input-error :messages="$errors->get('description')" class="mt-1" /></div>
    <div><label for="instructions" class="block text-sm font-medium">Petunjuk pengerjaan</label>
        <textarea id="instructions" name="instructions" rows="3" class="{{ $input }}">{{ old('instructions', $exercise->instructions) }}</textarea><x-input-error :messages="$errors->get('instructions')" class="mt-1" /></div>

    <div class="space-y-3">
        <div class="flex items-center gap-2"><input type="hidden" name="is_randomized" value="0">
            <input id="is_randomized" type="checkbox" name="is_randomized" value="1" @checked(old('is_randomized', $exercise->is_randomized)) class="rounded border-slate-300">
            <label for="is_randomized" class="text-sm font-medium">Acak urutan soal saat siswa memulai</label></div>
        @if ($isEdit)
            <div><div class="flex items-center gap-2"><input type="hidden" name="is_published" value="0">
                <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $exercise->is_published)) class="rounded border-slate-300">
                <label for="is_published" class="text-sm font-medium">Terbitake (katon kanggo siswa)</label></div>
                <x-input-error :messages="$errors->get('is_published')" class="mt-1" /></div>
        @else
            <p class="text-xs text-slate-500">Latihan anyar dadi Draft. Terbitake sawise soal ditambahake.</p>
        @endif
    </div>

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.exercises.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
