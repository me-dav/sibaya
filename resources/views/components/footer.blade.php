<footer class="mt-auto bg-sibaya-cream">
    {{-- Siluet gunung (placeholder sampai aset final) --}}
    <svg viewBox="0 0 1440 140" preserveAspectRatio="none" class="block h-20 w-full sm:h-28" aria-hidden="true">
        <path fill="#6F93C4" opacity=".55" d="M0 90 L120 50 L200 80 L330 30 L470 85 L600 60 L760 100 L920 45 L1080 90 L1240 55 L1440 95 V140 H0Z"/>
        <path fill="#12376B" d="M0 105 C180 70 380 70 560 98 S960 135 1160 92 S1380 78 1440 98 V140 H0Z"/>
    </svg>
    <div class="bg-sibaya-navy-800 text-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-6 px-4 pb-8 pt-2 text-center sm:px-6 lg:flex-row lg:justify-between lg:text-left lg:px-8">
            <x-logo class="hidden lg:inline-flex" />
            <div>
                <p class="font-script text-2xl leading-snug text-sibaya-gold-soft sm:text-3xl">
                    @foreach (config('sibaya.footer_quote') as $line){{ $line }}<br>@endforeach
                </p>
                <div class="mx-auto mt-3 flex items-center justify-center gap-3 text-sibaya-gold lg:mx-0" aria-hidden="true">
                    <span class="h-px w-16 bg-white/40"></span><span>❖</span><span class="h-px w-16 bg-white/40"></span>
                </div>
            </div>
            <x-logo class="lg:hidden" />
        </div>
        <p class="border-t border-white/10 py-3 text-center text-xs text-white/70">
            &copy; {{ date('Y') }} {{ config('sibaya.name') }} &middot; {{ config('sibaya.tagline') }}
        </p>
    </div>
</footer>
