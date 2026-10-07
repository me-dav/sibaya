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
<body class="relative min-h-screen bg-cream-100 font-sans text-sibaya-navy-900 antialiased">

    <div class="relative z-10 flex min-h-screen flex-col items-center px-4 pb-32 pt-10 sm:pt-16">
        <a href="{{ route('home') }}" class="mb-6 text-center" aria-label="siBAYA, kembali ke Beranda">
            <span class="font-display text-5xl font-extrabold leading-none tracking-tight text-navy-800">Si<span class="text-gold-500">BAYA</span></span>
            <span class="mt-1 block text-xs font-semibold text-navy-600">Sinau Basa lan Budaya Jawa</span>
        </a>

        <main class="w-full max-w-md rounded-3xl border border-sibaya-navy-100 bg-white p-6 shadow-lg shadow-navy-900/5 sm:p-8">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
