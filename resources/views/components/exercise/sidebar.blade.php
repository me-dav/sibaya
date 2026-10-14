@props(['active' => null])
@php
    $types = \App\Enums\ExerciseType::cases();
    $item = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500';
@endphp

<aside class="sticky top-[72px] hidden h-[calc(100vh-72px)] w-64 shrink-0 flex-col overflow-hidden border-r border-b border-navy-100 bg-white/70 lg:flex" aria-label="Menu Gladhèn Mandiri">
    <div class="px-5 pt-5">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-navy-700 hover:text-navy-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500">
            <x-icon name="arrow-left" size="h-4 w-4" /> Kembali ke Beranda
        </a>
        <hr class="my-4 border-navy-100">
        <h2 class="font-display text-xl font-extrabold text-navy-800">Gladhèn Mandiri</h2>
    </div>

    <nav class="mt-3 space-y-1 px-3" aria-label="Jenis latihan">
        <a href="{{ route('exercises.index') }}" @if (! $active) aria-current="page" @endif
           class="{{ $item }} {{ ! $active ? 'bg-navy-100 text-navy-800' : 'text-navy-700 hover:bg-navy-50' }}">
            <x-icon name="grid" /> Dashboard
        </a>
        @foreach ($types as $type)
            @php $on = $active === $type; @endphp
            <a href="{{ route('exercises.index', ['jenis' => $type->value]) }}" @if ($on) aria-current="page" @endif
               class="{{ $item }} {{ $on ? 'bg-navy-100 text-navy-800' : 'text-navy-700 hover:bg-navy-50' }}">
                <x-icon :name="$type->icon()" /> {{ $type->label() }}
            </a>
        @endforeach
    </nav>

    {{-- Dekorasi bawah: siluet candi + kutipan (ganti dengan aset final nanti) --}}
</aside>
