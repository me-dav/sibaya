<x-guest-layout>
    <x-slot name="title">Mlebu | siBAYA</x-slot>

    <h1 class="font-display text-2xl font-extrabold text-navy-800">Mlebu</h1>
    <p class="mt-1 text-sm text-navy-600">Masuk dengan akun siBAYA kamu.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Tembung sandi (kata sandi)" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-navy-700">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-navy-200 text-navy-800 focus:ring-gold-400">
                Eling aku
            </label>

            @if (Route::has('password.request'))
                <a class="rounded text-sm font-semibold text-navy-600 underline-offset-2 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500" href="{{ route('password.request') }}">
                    Lali sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full">Mlebu</x-primary-button>

        <p class="text-center text-sm text-navy-700">
            Durung duwe akun?
            <a href="{{ route('register') }}" class="font-bold text-navy-800 underline-offset-2 hover:underline">Daftar</a>
        </p>
    </form>
</x-guest-layout>
