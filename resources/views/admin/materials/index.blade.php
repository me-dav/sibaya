<x-layouts.admin title="Materi">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-sibaya-navy-800">Materi</h1>
        <a href="{{ route('admin.materials.create') }}" class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Tambah Materi</a>
    </div>

    @if (session('status'))<div role="status" class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">{{ session('status') }}</div>@endif
    @if (session('error'))<div role="alert" class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">{{ session('error') }}</div>@endif

    <form method="GET" class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div>
            <label for="q" class="block text-sm font-medium">Golek judul</label>
            <input id="q" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
        </div>
        <div>
            <label for="type" class="block text-sm font-medium">Jenis</label>
            <select id="type" name="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Kabeh</option>
                @foreach ($types as $type)<option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium">Status</label>
            <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">Kabeh</option>
                <option value="published" @selected(request('status') === 'published')>Terbit</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
        </div>
        <div class="flex items-end"><button class="w-full rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Filter</button></div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th scope="col" class="px-4 py-3 font-semibold">Judul</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Jenis</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Media</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Diubah</th>
                    <th scope="col" class="px-4 py-3 font-semibold"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($materials as $material)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $material->title }}</td>
                        <td class="px-4 py-3">{{ $material->rinenggaType?->name }}</td>
                        <td class="px-4 py-3">
                            @if ($material->is_published)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Terbit</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            {{ collect([$material->image_path ? 'Gambar' : null, $material->audio_path ? 'Audio' : null, $material->video_url ? 'Video' : null])->filter()->join(', ') ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $material->updated_at?->format('d M Y H:i') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('learning.show', $material) }}" class="text-sibaya-navy-700 hover:underline">Delengi</a>
                            <a href="{{ route('admin.materials.edit', $material) }}" class="ml-3 text-sibaya-navy-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('admin.materials.destroy', $material) }}" class="inline" data-confirm="Busak materi {{ $material->title }}? Tindakan iki ora bisa dibatalake." onsubmit="return confirm(this.dataset.confirm)">
                                @csrf @method('DELETE')
                                <button type="submit" class="ml-3 text-red-700 hover:underline">Busak</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Durung ana materi. Klik &ldquo;Tambah Materi&rdquo; kanggo miwiti.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $materials->links() }}</div>
</x-layouts.admin>
