<x-layouts.public :title="$title">
    <section class="mx-auto max-w-2xl px-4 py-20 text-center">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-navy-100 text-navy-700">
            <x-icon :name="$icon ?? 'book'" size="h-8 w-8" />
        </span>
        <h1 class="mt-4 font-display text-4xl font-extrabold text-navy-800">{{ $title }}</h1>
        <p class="mt-2 text-navy-700">Kaca iki lagi disiapake. Mangga bali menyang Beranda lan coba menu liyane. (Halaman ini sedang disiapkan.)</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-navy-800 px-6 py-2.5 text-sm font-bold text-white hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
            <x-icon name="arrow-left" size="h-4 w-4" /> Bali menyang Beranda
        </a>
    </section>
</x-layouts.public>
