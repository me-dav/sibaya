@props(['items' => []])
<nav aria-label="Jejak halaman" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-2 text-sm text-sibaya-ink/70">
        @foreach ($items as $label => $url)
            <li class="flex items-center gap-2">
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-sibaya-navy-800 hover:underline">{{ $label }}</a>
                    <span aria-hidden="true">/</span>
                @else
                    <span aria-current="page" class="font-medium text-sibaya-navy-800">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
