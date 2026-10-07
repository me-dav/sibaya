<footer class="bg-sibaya-navy-900 text-sibaya-navy-200">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 md:grid-cols-3">
            <div>
                <p class="font-display text-3xl font-extrabold text-white">Si<span class="text-sibaya-gold-400">BAYA</span></p>
                <p class="mt-1 text-sm">Sinau Basa lan Budaya Jawa</p>
            </div>

            <nav aria-label="Pranala footer">
                <p class="font-bold text-white">Pranala</p>
                <ul class="mt-2 space-y-1 text-sm grid grid-cols-2">
                    <li><a class="hover:text-sibaya-gold-300" href="{{ route('home') }}">Beranda</a></li>
                    @if (Route::has('learning.index'))<li><a class="hover:text-sibaya-gold-300" href="{{ route('learning.index') }}">Ruang Piwulang</a></li>@endif
                    @if (Route::has('exercises.index'))<li><a class="hover:text-sibaya-gold-300" href="{{ route('exercises.index') }}">Gladhèn Mandiri</a></li>@endif
                    @if (Route::has('evaluations.index'))<li><a class="hover:text-sibaya-gold-300" href="{{ route('evaluations.index') }}">Evaluasi</a></li>@endif
                    @if (Route::has('puzzles.index'))<li><a class="hover:text-sibaya-gold-300" href="{{ route('puzzles.index') }}">Wahana Teka-teki</a></li>@endif
                </ul>
            </nav>

            <p class="text-sm italic text-sibaya-gold-300 md:text-right">“Nguri-uri basa, nguri-uri budaya.”</p>
        </div>

        <p class="mt-8 border-t border-white/10 pt-4 text-xs text-navy-200">
            &copy; {{ date('Y') }} siBAYA. Sinau Basa lan Budaya Jawa.
        </p>
    </div>
</footer>
