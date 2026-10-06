<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'siBAYA') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen bg-cream-100 font-sans text-navy-900 antialiased">

    {{-- Siluet candi (placeholder SVG; ganti dengan aset final nanti) --}}
    <svg aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-40 w-full text-navy-100 sm:h-56"
         viewBox="0 0 1200 220" preserveAspectRatio="xMidYMax slice" fill="currentColor">
        <path d="M0 220V170c60-25 120-10 180-30 40-14 70-50 110-50s60 30 100 40c50 12 90-20 130-20s70 25 110 30c60 8 100-40 160-40 70 0 90 50 150 60 70 12 120-20 170-20 40 0 70 15 90 20v60z"/>
        <path d="M560 220v-30h-30v-22h20v-26h24v-30h16v-34h20v-24h20v24h20v34h16v30h24v26h20v22h-30v30z" opacity=".75"/>
    </svg>

    <div class="relative z-10 flex min-h-screen flex-col items-center px-4 pb-32 pt-10 sm:pt-16">
        <a href="{{ route('home') }}" class="mb-6 text-center" aria-label="siBAYA, kembali ke Beranda">
            <span class="font-display text-5xl font-extrabold leading-none tracking-tight text-navy-800">Si<span class="text-gold-500">BAYA</span></span>
            <span class="mt-1 block text-xs font-semibold text-navy-600">Sinau Basa lan Budaya Jawa</span>
        </a>

        <main class="w-full max-w-md rounded-3xl border border-navy-100 bg-white p-6 shadow-lg shadow-navy-900/5 sm:p-8">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
