@props(['src', 'label' => 'Rekaman audio'])

<div {{ $attributes->class(['rounded-2xl border border-emerald-200 bg-emerald-50 p-3']) }}>
    <p class="mb-2 flex items-center gap-2 text-sm font-bold text-emerald-900">
        <x-icon name="headphones" /> {{ $label }}
    </p>
    {{-- Tanpa tombol unduh; kontrol dapat dioperasikan keyboard --}}
    <audio controls preload="metadata" controlsList="nodownload noplaybackrate" oncontextmenu="return false"
           class="w-full" src="{{ $src }}">
        Browser sampeyan ora ndhukung audio.
    </audio>
    <p class="mt-1 text-xs text-emerald-800">Audio bisa dirungokake baleni kaping pirang-pirang.</p>
</div>
