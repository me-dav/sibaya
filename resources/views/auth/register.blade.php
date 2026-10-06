<x-guest-layout>
    <x-slot name="title">Daftar | siBAYA</x-slot>

    <h1 class="font-display text-2xl font-extrabold text-navy-800">Daftar</h1>
    <p class="mt-1 text-sm text-navy-600">Buat akun siswa untuk mulai sinau.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Jeneng lengkap (nama lengkap)" />
            <x-text-input id="name" class="mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="class_name" value="Kelas" />
                <x-text-input id="class_name" class="mt-1" type="text" name="class_name" :value="old('class_name')" placeholder="X-1" required />
                <x-input-error :messages="$errors->get('class_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="school_name" value="Sekolah" />
                <x-text-input id="school_name" class="mt-1" type="text" name="school_name" :value="old('school_name')" required />
                <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="password" value="Tembung sandi (kata sandi)" />
            <x-text-input id="password" class="mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Baleni tembung sandi (konfirmasi)" />
            <x-text-input id="password_confirmation" class="mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Daftar</x-primary-button>

        <p class="text-center text-sm text-navy-700">
            Wis duwe akun?
            <a href="{{ route('login') }}" class="font-bold text-navy-800 underline-offset-2 hover:underline">Mlebu</a>
        </p>
    </form>
</x-guest-layout>
