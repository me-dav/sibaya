@props(['type', 'material' => null])
@php
    $themes = [
        'paribasan'     => ['box' => 'bg-amber-50 border-amber-200',   'icon' => 'text-amber-600',  'path' => 'M5 19c8 0 14-6 14-14C11 5 5 11 5 19zM5 19L14 10'],
        'bebasan'       => ['box' => 'bg-green-50 border-green-200',   'icon' => 'text-green-700',  'path' => 'M9 11a3 3 0 100-6 3 3 0 000 6zM17 12a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM3 20c0-3 2.7-5 6-5s6 2 6 5M15 15c3 0 6 1.5 6 5'],
        'saloka'        => ['box' => 'bg-blue-50 border-blue-200',     'icon' => 'text-blue-700',   'path' => 'M3 19l6-10 4 6 3-4 5 8z'],
        'pepindhan'     => ['box' => 'bg-purple-50 border-purple-200', 'icon' => 'text-purple-700', 'path' => 'M12 3c2 2 2 4 0 6-2-2-2-4 0-6zM3 12c2-2 4-2 6 0-2 2-4 2-6 0zM21 12c-2-2-4-2-6 0 2 2 4 2 6 0zM12 21c-2-2-2-4 0-6 2 2 2 4 0 6z'],
        'sanepa'        => ['box' => 'bg-orange-50 border-orange-200', 'icon' => 'text-orange-600', 'path' => 'M12 16a4 4 0 100-8 4 4 0 000 8zM12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2'],
        'tembung-entar' => ['box' => 'bg-teal-50 border-teal-200',     'icon' => 'text-teal-700',   'path' => 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z'],
    ];
    $theme = $themes[$type->slug] ?? ['box' => 'bg-slate-50 border-slate-200', 'icon' => 'text-slate-600', 'path' => 'M5 4h14v16H5zM9 8h6M9 12h6'];
@endphp
<article class="flex flex-col rounded-2xl border p-5 {{ $theme['box'] }} hover:scale-[102%] hover:shadow-md transition duration-200 ease-in">
    <svg class="h-10 w-10 {{ $theme['icon'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $theme['path'] }}"/></svg>
    <h3 class="mt-4 text-lg font-bold text-sibaya-navy-800">{{ $type->name }}</h3>
    <p class="mt-2 flex-1 text-sm leading-relaxed text-sibaya-ink/80">{{ $type->short_description }}</p>
    @if ($material)
        <a href="{{ route('learning.show', $material) }}" aria-label="Waca materi {{ $type->name }}"
           class="mt-5 inline-flex w-fit items-center gap-2 rounded-full bg-sibaya-navy-800 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sibaya-navy-700">
            Waca <span aria-hidden="true">&rarr;</span>
        </a>
    @else
        <span class="mt-5 inline-flex w-fit rounded-full bg-white px-4 py-2 text-xs font-medium text-sibaya-ink/60 ring-1 ring-slate-200">Materi durung kasedhiya</span>
    @endif
</article>
