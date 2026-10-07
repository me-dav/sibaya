@php use App\Support\EvaluationRules; use App\Support\Labels; @endphp
<x-layouts.admin title="Evaluasi">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-sibaya-navy-800">Evaluasi (UTS &amp; UAS)</h1>
        <a href="{{ route('admin.evaluations.create') }}" class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Tambah Evaluasi</a>
    </div>
    <x-flash />

    <form method="GET" class="mt-6 flex items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div><label for="type" class="block text-sm font-medium">Jenis</label>
            <select id="type" name="type" class="mt-1 rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                @foreach (EvaluationRules::types() as $v => $l)<option value="{{ $v }}" @selected(request('type') === $v)>{{ $l }}</option>@endforeach</select></div>
        <button class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr>
                <th scope="col" class="px-4 py-3">Judul</th><th scope="col" class="px-4 py-3">Jenis</th><th scope="col" class="px-4 py-3">Durasi</th>
                <th scope="col" class="px-4 py-3">Soal</th><th scope="col" class="px-4 py-3">Percobaan</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($evaluations as $e)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $e->title }}</td>
                        <td class="px-4 py-3 uppercase">{{ Labels::value($e->type) }}</td>
                        <td class="px-4 py-3">{{ $e->duration_minutes }} menit</td>
                        <td class="px-4 py-3">{{ $e->questions_count }}</td>
                        <td class="px-4 py-3">{{ $e->attempts_count }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $e->is_published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $e->is_published ? 'Terbit' : 'Draft' }}</span></td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.evaluations.show', $e) }}" class="text-sibaya-navy-700 hover:underline">Kelola Soal</a>
                            <a href="{{ route('admin.evaluations.results', $e) }}" class="ml-3 text-sibaya-navy-700 hover:underline">Hasil</a>
                            <a href="{{ route('admin.evaluations.edit', $e) }}" class="ml-3 text-sibaya-navy-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('admin.evaluations.destroy', $e) }}" class="inline" data-confirm="Busak evaluasi {{ $e->title }}?" onsubmit="return confirm(this.dataset.confirm)">@csrf @method('DELETE')
                                <button class="ml-3 text-red-700 hover:underline">Busak</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Durung ana evaluasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $evaluations->links() }}</div>
</x-layouts.admin>
