@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-xl border-navy-200 bg-white text-navy-900 shadow-sm placeholder:text-navy-200 focus:border-navy-500 focus:ring-2 focus:ring-gold-400']) }}>
