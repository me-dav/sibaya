@props(['title' => 'Gladhèn Mandiri', 'active' => null])

<x-layouts.public :title="$title">
    <div class="mx-auto flex w-full max-w-[1500px]">
        <x-exercise.sidebar :active="$active" />

        <div class="min-w-0 flex-1 px-4 py-6 sm:px-6">
            @if (session('error'))
                <div role="alert" class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-900">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</x-layouts.public>
