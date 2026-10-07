@props(['summary'])
@if ($summary)
    @if ($summary['items'])
        <ul class="space-y-3">
            @foreach ($summary['items'] as $r)
                <li class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-sibaya-navy-800">
                                {{ $r['material']->title }}
                                <span class="text-xs font-normal text-slate-600">({{ $r['material']->rinenggaType?->name }})</span>
                                @unless ($r['is_read'])<span class="ml-1 rounded-full bg-sibaya-navy-800 px-2 py-0.5 text-xs font-semibold text-white">Anyar</span>@endunless
                            </p>
                            <p class="mt-1 text-sm leading-relaxed">{{ $r['reason'] }}</p>
                        </div>
                        <a href="{{ route('recommendations.open', $r['id']) }}"
                           class="whitespace-nowrap rounded-full bg-sibaya-navy-800 px-4 py-2 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Waca Materi &rarr;</a>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p class="rounded-xl bg-sibaya-sky-100/70 p-4 text-sm leading-relaxed" role="status">
            {{ $summary['message'] }}
            @if (in_array($summary['state'], ['insufficient', 'no_material'], true))
                <a href="{{ route('learning.index') }}" class="ml-1 font-semibold text-sibaya-navy-700 underline">Buka Ruang Piwulang</a>
            @endif
        </p>
    @endif
@endif
