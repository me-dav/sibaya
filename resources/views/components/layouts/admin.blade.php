@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' | ' : '' }}Admin {{ config('sibaya.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-sibaya-ink antialiased" x-data="{ mobileNav: false }">
    <a href="#admin-konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-white focus:px-4 focus:py-2">
        Lompat ke konten
    </a>

    <div class="flex min-h-screen">
        {{-- Desktop Sidebar --}}
        <x-admin-sidebar class="hidden w-64 shrink-0 lg:flex" />

        {{-- Mobile Drawer Overlay --}}
        <div x-show="mobileNav" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <div x-show="mobileNav"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60" @click="mobileNav = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileNav"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative mr-16 flex w-full max-w-xs flex-1">
                    <x-admin-sidebar class="w-full" />
                </div>
            </div>
        </div>

        {{-- Main Area --}}
        <div class="flex flex-1 flex-col min-w-0">
            {{-- Top Navbar Mobile / Header --}}
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button type="button" @click="mobileNav = true" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" aria-label="Buka menu navigasi">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    @if($title)
                        <h2 class="text-lg font-semibold text-sibaya-navy-800 truncate">{{ $title }}</h2>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden text-xs text-slate-500 sm:inline">{{ auth()->user()?->name }}</span>
                    <a href="{{ route('home') }}" class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                        Ke Beranda
                    </a>
                </div>
            </header>

            {{-- Slot Konten --}}
            <main id="admin-konten" class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
