@php
    use Illuminate\Support\Facades\Storage;
    $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700';
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    @if ($errors->any())
        <div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="rinengga_type_id" class="block text-sm font-medium">Jenis Basa Rinengga <span class="text-red-600">*</span></label>
            <select id="rinengga_type_id" name="rinengga_type_id" required class="{{ $input }}">
                <option value="">Pilih jenis</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected(old('rinengga_type_id', $material->rinengga_type_id) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('rinengga_type_id')" class="mt-1" />
        </div>
        <div>
            <label for="title" class="block text-sm font-medium">Judul <span class="text-red-600">*</span></label>
            <input id="title" name="title" value="{{ old('title', $material->title) }}" required class="{{ $input }}">
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium">Slug (URL)</label>
        <input id="slug" name="slug" value="{{ old('slug', $material->slug) }}" aria-describedby="slug-hint" class="{{ $input }}">
        <p id="slug-hint" class="mt-1 text-xs text-slate-500">Kosongkan untuk dibuat otomatis dari judul.</p>
        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium">Deskripsi singkat <span class="text-red-600">*</span></label>
        <textarea id="description" name="description" rows="2" required class="{{ $input }}">{{ old('description', $material->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    <div>
        <label for="content" class="block text-sm font-medium">Isi materi <span class="text-red-600">*</span></label>
        <textarea id="content" name="content" rows="12" required aria-describedby="md-hint" class="{{ $input }} font-mono">{{ old('content', $material->content) }}</textarea>
        <p id="md-hint" class="mt-1 text-xs text-slate-500">Mendukung Markdown (## judul, **tebal**, *miring*, daftar). HTML mentah otomatis dibuang demi keamanan.</p>
        <x-input-error :messages="$errors->get('content')" class="mt-1" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="examples" class="block text-sm font-medium">Tuladha (opsional)</label>
            <textarea id="examples" name="examples" rows="6" class="{{ $input }} font-mono">{{ old('examples', $material->examples) }}</textarea>
            <x-input-error :messages="$errors->get('examples')" class="mt-1" />
        </div>
        <div>
            <label for="summary" class="block text-sm font-medium">Rangkuman (opsional)</label>
            <textarea id="summary" name="summary" rows="6" class="{{ $input }} font-mono">{{ old('summary', $material->summary) }}</textarea>
            <x-input-error :messages="$errors->get('summary')" class="mt-1" />
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="image" class="block text-sm font-medium">Gambar (jpg, png, webp; maks 2 MB)</label>
            <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
            @if ($material->image_path)
                <img src="{{ Storage::disk('public')->url($material->image_path) }}" alt="Gambar materi saat ini" class="mt-2 h-24 rounded-lg object-cover">
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300"> Busak gambar iki</label>
            @endif
            <x-input-error :messages="$errors->get('image')" class="mt-1" />
        </div>
        <div>
            <label for="audio" class="block text-sm font-medium">Audio (mp3, wav, ogg; maks 10 MB)</label>
            <input id="audio" type="file" name="audio" accept="audio/mpeg,audio/wav,audio/ogg" class="mt-1 block w-full text-sm">
            @if ($material->audio_path)
                <audio controls controlsList="nodownload" class="mt-2 w-full"><source src="{{ Storage::disk('public')->url($material->audio_path) }}"></audio>
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_audio" value="1" class="rounded border-slate-300"> Busak audio iki</label>
            @endif
            <x-input-error :messages="$errors->get('audio')" class="mt-1" />
        </div>
    </div>

    <div>
        <label for="video_url" class="block text-sm font-medium">URL video (opsional)</label>
        <input id="video_url" type="url" name="video_url" value="{{ old('video_url', $material->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." aria-describedby="video-hint" class="{{ $input }}">
        <p id="video-hint" class="mt-1 text-xs text-slate-500">Harus https. YouTube dan Vimeo ditampilkan sebagai video tertanam; URL lain tampil sebagai link.</p>
        <x-input-error :messages="$errors->get('video_url')" class="mt-1" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_published" value="0">
        <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $material->is_published)) class="rounded border-slate-300">
        <label for="is_published" class="text-sm font-medium">Terbitake (katon kanggo siswa)</label>
    </div>

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.materials.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
