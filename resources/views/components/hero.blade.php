@props(['badge', 'title', 'subtitle' => null, 'image' => null])
<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-50 via-sibaya-sky-100 to-sibaya-sky-200 shadow-sm ring-1 ring-sibaya-sky-200']) }}>
    <div class="grid items-center gap-6 p-6 sm:p-8 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <span class="inline-block rounded-full bg-sibaya-navy-800 px-4 py-1 text-sm font-medium text-white">{{ $badge }}</span>
            <h1 class="mt-3 text-4xl font-extrabold text-sibaya-navy-800 sm:text-5xl">{{ $title }}</h1>
            @if ($subtitle)<p class="mt-1 text-2xl font-bold text-sibaya-navy-800">{{ $subtitle }}</p>@endif
            <div class="mt-4 max-w-xl leading-relaxed">{{ $slot }}</div>
        </div>
        {{-- Placeholder ilustrasi: ganti dengan aset final --}}
        <div class="hidden lg:col-span-2 lg:block">
            <img src="{{ $image ?? asset('images/placeholders/hero-siswa.svg') }}" alt="Ilustrasi siswa SMA belajar (placeholder)" class="mx-auto max-h-64 w-auto rounded-2xl" width="400" height="420">
        </div>
    </div>
</section>
