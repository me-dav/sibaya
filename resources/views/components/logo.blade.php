@props(['size' => 'md'])
@php
    $text = $size === 'lg' ? 'text-6xl sm:text-7xl' : 'text-3xl';
    $icon = $size === 'lg' ? 'h-14 w-14 sm:h-16 sm:w-16' : 'h-10 w-10';
    $sub  = $size === 'lg' ? 'text-sm' : 'text-[10px]';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    {{-- Placeholder logo teratai; ganti dengan aset final --}}
    <svg class="{{ $icon }} shrink-0 text-sibaya-gold" viewBox="0 0 48 48" fill="currentColor" aria-hidden="true">
        <ellipse cx="24" cy="18" rx="5" ry="12"/>
        <ellipse cx="14" cy="26" rx="4.5" ry="10" transform="rotate(-35 14 26)"/>
        <ellipse cx="34" cy="26" rx="4.5" ry="10" transform="rotate(35 34 26)"/>
        <path d="M8 40c6 5 26 5 32 0" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>
    <span class="leading-none">
        <span class="block font-extrabold tracking-wide {{ $text }}">Si<span class="tracking-wider">BAYA</span></span>
        <span class="mt-1 block font-medium {{ $sub }}">{{ config('sibaya.tagline') }}</span>
    </span>
</span>
