@props(['title', 'icon'])
@php
    $icons = [
        'phone'  => 'M8 2h8a1 1 0 011 1v18a1 1 0 01-1 1H8a1 1 0 01-1-1V3a1 1 0 011-1zM11 18h2',
        'bulb'   => 'M9 18h6M10 21h4M12 3a6 6 0 00-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0012 3z',
        'lotus'  => 'M12 4c2 2 3 4.5 3 7s-1 4-3 5c-2-1-3-2.5-3-5s1-5 3-7zM4 10c3 0 5 2 6 5M20 10c-3 0-5 2-6 5M5 19c4 2 10 2 14 0',
        'chart'  => 'M5 21V11M12 21V4M19 21v-7',
    ];
@endphp
<div class="flex items-start gap-4">
    <svg class="mt-1 h-9 w-9 shrink-0 text-sibaya-navy-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icons[$icon] }}"/></svg>
    <div>
        <h3 class="text-sm font-bold text-sibaya-navy-800">{{ $title }}</h3>
        <p class="mt-1 text-sm leading-relaxed text-sibaya-ink/80">{{ $slot }}</p>
    </div>
</div>
