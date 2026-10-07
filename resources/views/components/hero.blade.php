@props(['badge', 'title', 'subtitle' => null, 'image' => null])

<section
    {{ $attributes->merge([
        'class' => 'relative overflow-hidden rounded-3xl bg-cover bg-center shadow-sm ring-1 ring-sibaya-sky-200',
        'style' => "background-image: url('" . ($image ?? asset('images/hero.jpeg')) . "')"
    ]) }}
>


    <div class="relative grid items-center gap-6 p-6 sm:p-8 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <span class="inline-block rounded-full bg-sibaya-navy-800 px-4 py-1 text-sm font-medium text-white">
                {{ $badge }}
            </span>

            <h1 class="mt-3 text-4xl font-extrabold text-sibaya-navy-800 sm:text-5xl">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="mt-1 text-2xl font-bold text-sibaya-navy-800">
                    {{ $subtitle }}
                </p>
            @endif

            <div class="mt-4 max-w-xl leading-relaxed">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>