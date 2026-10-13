@props(['title', 'subtitle', 'href' => '#', 'theme' => 'ruang', 'icon' => 'document', 'items' => []])
@php
    $t = [
        'ruang'   => ['box' => 'bg-sibaya-ruang-bg border-sibaya-ruang-line',     'badge' => 'bg-sibaya-ruang-accent',   'title' => 'text-sibaya-navy-800'],
        'gladhen' => ['box' => 'bg-sibaya-gladhen-bg border-sibaya-gladhen-line', 'badge' => 'bg-sibaya-gladhen-accent', 'title' => 'text-sibaya-navy-800'],
        'wahana'  => ['box' => 'bg-sibaya-wahana-bg border-sibaya-wahana-line',   'badge' => 'bg-sibaya-wahana-accent',  'title' => 'text-sibaya-wahana-ink'],
    ][$theme];
    $icons = [
        'headphones' => 'M3 14v-2a9 9 0 0118 0v2M3 14h3a1 1 0 011 1v4a1 1 0 01-1 1H5a2 2 0 01-2-2v-4zm18 0h-3a1 1 0 00-1 1v4a1 1 0 001 1h1a2 2 0 002-2v-4z',
        'document'   => 'M7 3h7l5 5v13H7zM14 3v5h5M10 13h6M10 17h6',
        'gamepad'    => 'M7 6h10a4 4 0 014 4v4a3 3 0 01-5.2 2L14 15h-4l-1.8 1A3 3 0 013 14v-4a4 4 0 014-4zM8 9v4M6 11h4M15 12h.01M18 10h.01',
    ];
@endphp
<article {{ $attributes->merge(['class' => "flex flex-col rounded-2xl border p-6 shadow-sm {$t['box']} hover:-translate-y-3 hover:shadow-md transition duration-300 ease-in"]) }}>
    <div class="flex items-center gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-white {{ $t['badge'] }}" aria-hidden="true">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icons[$icon] }}"/></svg>
        </span>
        <div>
            <h2 class="text-xl font-bold leading-tight {{ $t['title'] }}">{{ $title }}</h2>
            <p class="mt-0.5 text-sm font-medium text-sibaya-navy-800/80">{{ $subtitle }}</p>
        </div>
    </div>
    <ul class="mt-5 flex-1 space-y-1.5 text-sm text-sibaya-ink">
        @foreach ($items as $item)
            <li class="flex items-start gap-2">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-sibaya-navy-800" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                <span>{{ $item }}</span>
            </li>
        @endforeach
    </ul>
    <a href="{{ $href }}" aria-label="Masuk ke {{ $title }}"
       class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-sibaya-navy-800 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-sibaya-navy-700">
        Masuk
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
</article>
