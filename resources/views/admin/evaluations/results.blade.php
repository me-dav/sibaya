@php $fmt = fn ($n) => number_format((float) $n, 1, ',', '.'); @endphp
<x-layouts.admin :title="'Hasil: '.$evaluation->title">
    <x-breadcrumb :items="['Evaluasi' => route('admin.evaluations.index'), $evaluation->title => route('admin.evaluations.show', $evaluation), 'Hasil' => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">Hasil {{ $evaluation->title }}</h1>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total percobaan" :value="$total" />
        <x-stat-card label="Rata-rata nilai" :value="$average === null ? '-' : $fmt($average)" hint="Percobaan rampung" />
        <x-stat-card label="Persentase lulus" :value="$passRate === null ? '-' : $passRate.'%'" :hint="'KKM '.$evaluation->passing_score" />
    </div>

    <form method="GET" class="mt-6 flex items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div><label for="q" class="block text-sm font-medium">Siswa</label><input id="q" name="q" value="{{ request('q') }}" class="mt-1 rounded-lg border-slate-300 text-sm"></div>
        <button class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Golek</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Siswa</th><th scope="col" class="px-4 py-3">Kelas</th><th scope="col" class="px-4 py-3">Nilai</th>
                <th scope="col" class="px-4 py-3">Kelulusan</th><th scope="col" class="px-4 py-3">Bener/Salah</th><th scope="col" class="px-4 py-3">Status</th><th scope="col" class="px-4 py-3">Wektu</th><th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $a)
                    <tr><td class="px-4 py-3 font-medium">{{ $a->user?->name }}</td><td class="px-4 py-3">{{ $a->user?->class_name }}</td>
                        @php $st = $a->status instanceof \BackedEnum ? $a->status->value : (string) $a->status; @endphp
                        <td class="px-4 py-3">@if ($st === 'in_progress') - @elseif ($a->score >= $evaluation->passing_score) &#10003; Lulus @else &#10007; Durung lulus @endif</td>
                        <td class="px-4 py-3">{{ $a->correct_answers }} / {{ $a->wrong_answers }}</td><td class="px-4 py-3">{{ $st }}</td>
                        <td class="px-4 py-3">{{ $a->started_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.results.evaluations.show', $a) }}" class="text-sibaya-navy-700 hover:underline">Detail</a></td></tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">Durung ana percobaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
</x-layouts.admin>
