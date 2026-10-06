@php $fmt = fn ($n) => number_format((float) $n, 1, ',', '.'); @endphp
<x-layouts.admin :title="$student->name">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-sibaya-navy-800">{{ $student->name }}</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.students.edit', $student) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">Ubah</a>
            <form method="POST" action="{{ route('admin.students.toggle', $student) }}">@csrf @method('PATCH')
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold">{{ $student->is_active ? 'Nonaktifake' : 'Aktifake' }}</button></form>
            <form method="POST" action="{{ route('admin.students.destroy', $student) }}" data-confirm="Busak siswa {{ $student->name }}? Tindakan iki ora bisa dibatalake." onsubmit="return confirm(this.dataset.confirm)">@csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700">Busak</button></form>
        </div>
    </div>
    <x-flash />

    <dl class="mt-6 grid gap-4 rounded-xl bg-white p-5 text-sm shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div><dt class="text-slate-500">Email</dt><dd class="font-medium">{{ $student->email }}</dd></div>
        <div><dt class="text-slate-500">Kelas</dt><dd class="font-medium">{{ $student->class_name }}</dd></div>
        <div><dt class="text-slate-500">Sekolah</dt><dd class="font-medium">{{ $student->school_name }}</dd></div>
        <div><dt class="text-slate-500">Status</dt><dd class="font-medium">{{ $student->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>

    <section class="mt-8" aria-labelledby="h-lat">
        <h2 id="h-lat" class="text-lg font-bold text-sibaya-navy-800">Riwayat Gladhen <span class="text-sm font-normal text-slate-500">({{ $exerciseTotal }} percobaan, 10 terakhir)</span></h2>
        <div class="mt-3 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Latihan</th><th scope="col" class="px-4 py-3">Nilai</th><th scope="col" class="px-4 py-3">Bener/Salah</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3">Wektu</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($exerciseAttempts as $a)
                        <tr><td class="px-4 py-3">{{ $a->exercise?->title }}</td><td class="px-4 py-3 font-semibold">{{ $fmt($a->score) }}</td>
                            <td class="px-4 py-3">{{ $a->correct_answers }} / {{ $a->wrong_answers }}</td><td class="px-4 py-3">{{ $a->status }}</td>
                            <td class="px-4 py-3">{{ $a->started_at?->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.results.exercises.show', $a) }}" class="text-sibaya-navy-700 hover:underline">Detail</a></td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Durung ana riwayat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-8" aria-labelledby="h-eval">
        <h2 id="h-eval" class="text-lg font-bold text-sibaya-navy-800">Riwayat Evaluasi</h2>
        <ul class="mt-3 divide-y divide-slate-100 rounded-xl bg-white text-sm shadow-sm ring-1 ring-slate-200">
            @forelse ($evaluationAttempts as $a)
                <li class="flex justify-between px-4 py-3"><span>{{ $a->evaluation?->title }} ({{ $a->status }})</span><span class="font-semibold">Nilai {{ $fmt($a->score) }}</span></li>
            @empty
                <li class="px-4 py-6 text-center text-slate-500">Durung ana riwayat.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.admin>
