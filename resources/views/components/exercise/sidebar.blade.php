@props(['active' => null])
@php
    $types = \App\Enums\ExerciseType::cases();
    $item = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-navy-500';
@endphp

<aside class="sticky top-[72px] hidden h-[calc(100vh-72px)] w-64 shrink-0 flex-col overflow-hidden border-r border-navy-100 bg-white/70 lg:flex" aria-label="Menu Gladhèn Mandiri">
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
    <div class="relative mt-auto h-64 bg-gradient-to-t from-navy-900 via-navy-800 to-transparent">
        <svg aria-hidden="true" class="absolute inset-x-0 bottom-0 h-44 w-full text-navy-700/60" viewBox="0 0 260 180" preserveAspectRatio="xMidYMax slice" fill="currentColor">
            <path d="M0 180v-40c25-10 45-30 70-30s35 20 60 25c30 6 45-25 75-25 25 0 40 15 55 20v50z"/>
            <path d="M120 180v-22h-14v-14h10v-18h12v-18h8v-20h10v20h8v18h12v18h10v14h-14v22z" opacity=".8"/>
        </svg>
        <p class="absolute inset-x-0 bottom-6 px-6 text-center font-display text-lg italic text-white">“Ngudi ilmu,<br>nguri-uri budaya.”</p>
    </div>
</aside>
