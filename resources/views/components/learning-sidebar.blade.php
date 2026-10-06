@php
    $menu = [
        'Pambuka'                => '#pambuka',
        'Jenis-Jenis'            => '#jenis',
        'Ciri-Ciri lan Tuladha'  => '#ciri',
        'Basa Rinengga ing Teks' => '#teks',
        'Audio Nyimak'           => '#audio-nyimak',
        'Video Piwulang'         => '#video-piwulang',
        'Rangkuman Materi'       => '#rangkuman-materi',
    ];
@endphp
<nav aria-label="Menu Ruang Piwulang" {{ $attributes->merge(['class' => 'rounded-2xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200']) }}>
    <a href="{{ route('home') }}" class="text-sm text-sibaya-navy-700 hover:underline">&larr; Bali menyang Beranda</a>
    <p class="mt-4 text-lg font-bold text-sibaya-navy-800">Ruang Piwulang</p>
    <ul class="mt-2 space-y-1">
        @foreach ($menu as $label => $href)
            <li><a href="{{ $href }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-sibaya-ink hover:bg-sibaya-sky-100">{{ $label }}</a></li>
        @endforeach
    </ul>
    <p class="mt-6 rounded-xl bg-sibaya-navy-800 p-4 text-center font-script text-xl leading-snug text-sibaya-gold-soft">
        &ldquo;Nguri-uri basa nguri-uri budaya.&rdquo;
    </p>
</nav>
