@php $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700'; $isEdit = $student->exists; @endphp
<form method="POST" action="{{ $action }}" class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any())<div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>@endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div><label for="name" class="block text-sm font-medium">Jeneng <span class="text-red-600">*</span></label>
            <input id="name" name="name" value="{{ old('name', $student->name) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('name')" class="mt-1" /></div>
        <div><label for="email" class="block text-sm font-medium">Email <span class="text-red-600">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email', $student->email) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('email')" class="mt-1" /></div>
        <div><label for="class_name" class="block text-sm font-medium">Kelas <span class="text-red-600">*</span></label>
            <input id="class_name" name="class_name" value="{{ old('class_name', $student->class_name) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('class_name')" class="mt-1" /></div>
        <div><label for="school_name" class="block text-sm font-medium">Sekolah <span class="text-red-600">*</span></label>
            <input id="school_name" name="school_name" value="{{ old('school_name', $student->school_name) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('school_name')" class="mt-1" /></div>
        <div><label for="password" class="block text-sm font-medium">Sandi @unless($isEdit)<span class="text-red-600">*</span>@endunless</label>
            <input id="password" type="password" name="password" autocomplete="new-password" @unless($isEdit) required @endunless aria-describedby="pw-hint" class="{{ $input }}">
            @if ($isEdit)<p id="pw-hint" class="mt-1 text-xs text-slate-500">Kosongake yen ora arep ngganti sandi.</p>@else<span id="pw-hint"></span>@endif
            <x-input-error :messages="$errors->get('password')" class="mt-1" /></div>
        <div><label for="password_confirmation" class="block text-sm font-medium">Konfirmasi sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $input }}"></div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->is_active ?? true)) class="rounded border-slate-300">
        <label for="is_active" class="text-sm font-medium">Akun aktif</label>
    </div>

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.students.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
