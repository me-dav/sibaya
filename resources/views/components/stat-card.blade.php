@props(['label', 'value', 'hint' => null])
<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <p class="text-sm text-slate-600">{{ $label }}</p>
    <p class="mt-1 text-3xl font-extrabold text-sibaya-navy-800">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
</div>
