<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-full bg-navy-800 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-navy-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
