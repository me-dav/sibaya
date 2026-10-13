@props(['text'])
@php
    // Satu baris kosong = paragraf baru. Enter tunggal tetap jadi baris baru.
    $paragraphs = preg_split('/\R{2,}/', trim((string) $text));
@endphp

<div {{ $attributes->class(['space-y-4']) }}>
    @foreach ($paragraphs as $paragraph)
        <p class="whitespace-pre-line">{{ $paragraph }}</p>
    @endforeach
</div>