@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home'],
        ['label' => 'Siswa', 'route' => 'admin.students.index', 'match' => 'admin.students.*', 'icon' => 'users'],
        ['label' => 'Materi', 'route' => 'admin.materials.index', 'match' => 'admin.materials.*', 'icon' => 'book'],
        ['label' => 'Latihan (Gladhen)', 'route' => 'admin.exercises.index', 'match' => 'admin.exercises.*', 'icon' => 'edit'],
        ['label' => 'Hasil Latihan', 'route' => 'admin.results.index', 'match' => 'admin.results.*', 'icon' => 'award'],
        ['label' => 'Evaluasi (UTS/UAS)', 'route' => 'admin.evaluations.index', 'match' => 'admin.evaluations.*', 'icon' => 'clipboard'],
        ['label' => 'Wahana Teka-teki', 'route' => 'admin.puzzles.index', 'match' => 'admin.puzzles.*', 'icon' => 'puzzle'],
    ];
@endphp

<aside {{ $attributes->merge(['class' => 'flex flex-col bg-sibaya-navy-900 text-white']) }}>
    {{-- Header Sidebar / Logo --}}
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <x-logo />
            <span class="rounded bg-sibaya-gold px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-sibaya-navy-900">Admin</span>
        </a>
    </div>

    {{-- Menu Navigasi --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi admin">
        @foreach ($nav as $item)
            @php $active = request()->routeIs($item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               @if($active) aria-current="page" @endif
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $active ? 'bg-sibaya-navy-700 text-white font-semibold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                @if($item['icon'] === 'home')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                @elseif($item['icon'] === 'users')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                @elseif($item['icon'] === 'book')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                @elseif($item['icon'] === 'edit')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                @elseif($item['icon'] === 'award')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                @elseif($item['icon'] === 'clipboard')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                @elseif($item['icon'] === 'puzzle')
                    <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-sibaya-gold' : 'text-slate-400 group-hover:text-white' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>
                @endif
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Footer Sidebar: Profil & Keluar --}}
    <div class="border-t border-white/10 p-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-slate-300 hover:bg-white/10 hover:text-white">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            <span>Deleng Web Publik</span>
        </a>

        <div class="mt-2 flex items-center justify-between rounded-lg bg-white/5 px-3 py-2 text-xs">
            <div class="truncate">
                <p class="font-semibold text-white truncate">{{ auth()->user()?->name ?? 'Admin' }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()?->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" title="Metu / Logout" class="rounded p-1 text-slate-400 hover:bg-white/10 hover:text-white focus:outline-none">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
