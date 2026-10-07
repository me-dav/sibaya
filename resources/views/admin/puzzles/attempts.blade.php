@php $fmt = fn ($n) => number_format((float) $n, 1, ',', '.'); @endphp
<x-layouts.admin :title="'Riwayat: '.$puzzle->title">
    <x-breadcrumb :items="['Puzzle' => route('admin.puzzles.index'), $puzzle->title => null]" />
    <h1 class="mt-3 text-2xl font-bold text-sibaya-navy-800">Riwayat {{ $puzzle->title }}</h1>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total percobaan" :value="$total" />
        <x-stat-card label="Rata-rata skor" :value="$average === null ? '-' : $fmt($average)" />
        <x-stat-card label="Skor tertinggi" :value="$best === null ? '-' : $fmt($best)" />
    </div>

    <form method="GET" class="mt-6 flex items-end gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div><label for="q" class="block text-sm font-medium">Siswa</label><input id="q" name="q" value="{{ request('q') }}" class="mt-1 rounded-lg border-slate-300 text-sm"></div>
        <button class="rounded-lg bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white">Golek</button>
    </form>

    <div class="mt-6 overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left"><tr><th scope="col" class="px-4 py-3">Siswa</th><th scope="col" class="px-4 py-3">Kelas</th><th scope="col" class="px-4 py-3">Skor</th><th scope="col" class="px-4 py-3">Wektu</th><th scope="col" class="px-4 py-3">Rampung</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $a)
                    <tr><td class="px-4 py-3 font-medium">{{ $a->user?->name }}</td><td class="px-4 py-3">{{ $a->user?->class_name }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $fmt($a->score) }}</td><td class="px-4 py-3">{{ gmdate('i:s', (int) $a->duration_seconds) }}</td>
                        <td class="px-4 py-3">{{ $a->completed_at?->format('d M Y H:i') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Durung ana percobaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
</x-layouts.admin>
