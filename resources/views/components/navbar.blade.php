@php
    $link = fn (string $name) => Route::has($name) ? route($name) : '#';
    $profileUrl = Route::has('profile.show') ? route('profile.show') : (Route::has('profile.edit') ? route('profile.edit') : '#');
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 bg-sibaya-navy-800 text-white shadow-md">
    <div class="mx-auto flex h-[72px] max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="shrink-0 rounded focus-visible:outline" aria-label="{{ config('sibaya.name') }} - Beranda">
            <x-logo />
        </a>

        {{-- Menu desktop --}}
        <nav class="hidden items-center gap-7 lg:flex" aria-label="Menu utama">
            @foreach (config('sibaya.nav') as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ $link($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   class="border-b-2 py-2 text-sm font-medium transition-colors hover:text-sibaya-gold-soft {{ $active ? 'border-sibaya-gold text-white' : 'border-transparent text-white/90' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Autentikasi --}}
        <div class="flex items-center gap-2">
            @guest
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-medium transition hover:bg-white/10">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round"/></svg>
                    Login
                </a>
            @endguest

            @auth
                <div x-data="{ menu: false }" class="relative" @keydown.escape="menu = false" @click.outside="menu = false">
                    <button type="button" @click="menu = !menu" :aria-expanded="menu"
                            class="inline-flex items-center gap-2 rounded-full border border-white/40 px-4 py-2 text-sm font-medium transition hover:bg-white/10">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round"/></svg>
                        Profil
                    </button>
                    <div x-show="menu" x-cloak class="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl bg-white py-1 text-sm text-sibaya-ink shadow-lg ring-1 ring-black/5">
                        <p class="truncate px-4 py-2 text-xs text-slate-500">{{ auth()->user()->name }}</p>
                        <a href="{{ $profileUrl }}" class="block px-4 py-2 hover:bg-sibaya-sky-100">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-left hover:bg-sibaya-sky-100">Logout</button>
                        </form>
                    </div>
                </div>
            @endauth

            {{-- Tombol menu mobile --}}
            <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="menu-mobile"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg hover:bg-white/10 lg:hidden">
                <span class="sr-only">Buka menu</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/><path x-show="open" x-cloak d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Menu mobile --}}
    <nav id="menu-mobile" x-show="open" x-cloak class="border-t border-white/10 bg-sibaya-navy-900 px-4 py-3 lg:hidden" aria-label="Menu utama (seluler)">
        @foreach (config('sibaya.nav') as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ $link($item['route']) }}" @if($active) aria-current="page" @endif
               class="block rounded-lg px-3 py-3 text-sm font-medium {{ $active ? 'bg-white/10 text-sibaya-gold-soft' : 'text-white/90 hover:bg-white/5' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</header>
