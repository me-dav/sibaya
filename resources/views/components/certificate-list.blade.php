@props(['items'])
@if (count($items))
    <ul class="space-y-3">
        @foreach ($items as $i)
            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-green-50 p-4 ring-1 ring-green-200">
                <div>
                    <p class="font-semibold text-sibaya-navy-800">{{ $i['evaluation']->title }}
                        <span class="text-xs font-normal uppercase text-slate-600">({{ \App\Support\Labels::value($i['evaluation']->type) }})</span></p>
                    <p class="text-sm">&#10003; Lulus &middot; Nilai {{ number_format((float) $i['attempt']->score, 1, ',', '.') }} &middot; {{ \App\Services\CertificateService::predicateFor($i['attempt']->score) }}</p>
                </div>
                @if ($i['certificate'])
                    <a href="{{ route('certificates.show', $i['certificate']) }}" class="rounded-full bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Delengi / Cetak</a>
                @else
                    <form method="POST" action="{{ route('certificates.issue', $i['attempt']) }}">
                        @csrf
                        <button class="rounded-full bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Terbitake Sertifikat</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <p class="rounded-xl bg-sibaya-sky-100/70 p-4 text-sm" role="status">Durung ana sertifikat. Sertifikat kasedhiya yen kowe lulus evaluasi.</p>
@endif
