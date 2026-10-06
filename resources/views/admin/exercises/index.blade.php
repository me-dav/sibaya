@php use App\Support\Labels; @endphp
<x-layouts.admin title="Latihan">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-sibaya-navy-800">Gladhen</h1>
        <a href="{{ route('admin.exercises.create') }}" class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Tambah Latihan</a>
    </div>
    <x-flash />

    <form method="GET" class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div><label for="q" class="block text-sm font-medium">Golek judul</label><input id="q" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        <div><label for="type" class="block text-sm font-medium">Jenis</label>
            <select id="type" name="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                @foreach (Labels::exerciseTypes() as $v => $l)<option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div><label for="status" class="block text-sm font-medium">Status</label>
            <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                <option value="published" @selected(request('status') === 'published')>Terbit</option><option value="draft" @selected(request('status') === 'draft')>Draft</option></select></div>
        <div class="flex items-end"><button class="w-full rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Filter</button></div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Judul</th><th scope="col" class="px-4 py-3">Jenis</th><th scope="col" class="px-4 py-3">Soal</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($exercises as $e)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $e->title }}</td>
                        <td class="px-4 py-3">{{ Labels::exerciseTypes()[Labels::value($e->exercise_type)] ?? Labels::value($e->exercise_type) }}</td>
                        <td class="px-4 py-3">{{ $e->questions_count }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $e->is_published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $e->is_published ? 'Terbit' : 'Draft' }}</span></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.exercises.show', $e) }}" class="text-sibaya-navy-700 hover:underline">Kelola Soal</a>
                            <a href="{{ route('admin.exercises.edit', $e) }}" class="ml-3 text-sibaya-navy-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('admin.exercises.destroy', $e) }}" class="inline" data-confirm="Busak latihan {{ $e->title }} lan kabeh soale?" onsubmit="return confirm(this.dataset.confirm)">@csrf @method('DELETE')
                                <button class="ml-3 text-red-700 hover:underline">Busak</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Durung ana latihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $exercises->links() }}</div>
</x-layouts.admin>
