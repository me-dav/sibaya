@php $fmt = fn ($n) => $n === null ? '-' : number_format((float) $n, 1, ',', '.'); @endphp
<x-layouts.admin title="Dashboard">
    <h1 class="text-2xl font-bold text-sibaya-navy-800">Dashboard Admin</h1>
    <x-flash />

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik dasar">
        <x-stat-card label="Total siswa" :value="$summary['total_students']" :hint="$summary['active_students'].' akun aktif'" />
        <x-stat-card label="Total materi" :value="$summary['total_materials']" />
        <x-stat-card label="Total latihan" :value="$summary['total_exercises']" />
        <x-stat-card label="Total evaluasi" :value="$summary['total_evaluations']" />
        <x-stat-card label="Total attempt" :value="$summary['exercise_attempts'] + $summary['evaluation_attempts']"
                     :hint="$summary['exercise_attempts'].' latihan, '.$summary['evaluation_attempts'].' evaluasi'" />
        <x-stat-card label="Rata-rata nilai latihan" :value="$fmt($summary['avg_exercise'])" hint="Attempt selesai" />
        <x-stat-card label="Rata-rata nilai evaluasi" :value="$fmt($summary['avg_evaluation'])" hint="Attempt selesai" />
    </section>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200" aria-labelledby="h-eval">
            <h2 id="h-eval" class="text-lg font-bold text-sibaya-navy-800">Evaluasi Terbaru</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($evaluations as $e)
                    <li class="flex justify-between py-2"><span>{{ $e->title }}</span><span class="uppercase text-slate-500">{{ \App\Support\Labels::value($e->type) }}</span></li>
                @empty
                    <li class="py-4 text-slate-500">Durung ana evaluasi.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200" aria-labelledby="h-hasil">
            <h2 id="h-hasil" class="text-lg font-bold text-sibaya-navy-800">Aktivitas Hasil Terbaru</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($results as $r)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <span><span class="font-medium">{{ $r['student'] }}</span> &middot; {{ $r['kind'] }}: {{ $r['title'] }}</span>
                        <span class="whitespace-nowrap">
                            Nilai <strong>{{ $fmt($r['score']) }}</strong>
                            @if ($r['url'])<a href="{{ $r['url'] }}" class="ml-2 text-sibaya-navy-700 hover:underline">Detail</a>@endif
                        </span>
                    </li>
                @empty
                    <li class="py-4 text-slate-500">Durung ana hasil.</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts.admin>
