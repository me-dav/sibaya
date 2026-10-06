@php $fmt = fn ($n) => number_format((float) $n, 1, ',', '.'); @endphp
<x-layouts.admin title="Hasil Latihan">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Hasil Gladhen</h1>
    <x-flash />

    <form method="GET" class="mt-6 grid gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-5">
        <div><label for="q" class="block text-sm font-medium">Siswa</label><input id="q" name="q" value="{{ request('q') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm"></div>
        <div><label for="exercise" class="block text-sm font-medium">Latihan</label>
            <select id="exercise" name="exercise" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                @foreach ($exercises as $e)<option value="{{ $e->id }}" @selected(request('exercise') == $e->id)>{{ $e->title }}</option>@endforeach</select></div>
        <div><label for="class" class="block text-sm font-medium">Kelas</label>
            <select id="class" name="class" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                @foreach ($classes as $c)<option value="{{ $c }}" @selected(request('class') === $c)>{{ $c }}</option>@endforeach</select></div>
        <div><label for="status" class="block text-sm font-medium">Status</label>
            <select id="status" name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm"><option value="">Kabeh</option>
                @foreach (['completed' => 'Rampung', 'in_progress' => 'Durung rampung', 'abandoned' => 'Ditinggal'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach</select></div>
        <div class="flex items-end"><button class="w-full rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Filter</button></div>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Siswa</th><th scope="col" class="px-4 py-3">Kelas</th><th scope="col" class="px-4 py-3">Latihan</th>
                <th scope="col" class="px-4 py-3">Nilai</th><th scope="col" class="px-4 py-3">Bener/Salah</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3">Wektu</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $a)
                    <tr><td class="px-4 py-3 font-medium">{{ $a->user?->name }}</td><td class="px-4 py-3">{{ $a->user?->class_name }}</td><td class="px-4 py-3">{{ $a->exercise?->title }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fmt($a->score) }}</td><td class="px-4 py-3">{{ $a->correct_answers }} / {{ $a->wrong_answers }}</td>
                        <td class="px-4 py-3">{{ $a->status }}</td><td class="px-4 py-3">{{ $a->started_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.results.exercises.show', $a) }}" class="text-sibaya-navy-700 hover:underline">Detail</a></td></tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">Durung ana hasil.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
</x-layouts.admin>
