@php
    $isScramble = $play['type'] === 'susun_kata';
    $tiles = $isScramble ? collect($play['items'])->pluck('tiles')->all() : [];
@endphp
<x-layouts.public :title="$puzzle->title">
    <script>
        function puzzlePlay(cfg) {
            return {
                tiles: cfg.tiles, picks: cfg.tiles.map(() => []), dir: 'across',
                remaining: null, offset: cfg.now - Date.now(), submitting: false,
                init() {
                    if (cfg.deadline) { this.tick(); setInterval(() => this.tick(), 1000); }
                },
                tick() {
                    this.remaining = Math.max(0, Math.floor((cfg.deadline - (Date.now() + this.offset)) / 1000));
                    if (this.remaining === 0 && !this.submitting) {
                        this.submitting = true;
                        this.$nextTick(() => this.$refs.form.requestSubmit());
                    }
                },
                get label() {
                    if (this.remaining === null) return '';
                    const m = Math.floor(this.remaining / 60), s = this.remaining % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
                // --- susun kata
                pick(i, t) { if (!this.picks[i].includes(t)) this.picks[i].push(t); },
                undo(i) { this.picks[i].pop(); },
                reset(i) { this.picks[i] = []; },
                letters(i) { return this.picks[i].map(t => this.tiles[i][t]); },
                value(i) { return this.letters(i).join(''); },
                // --- teka-teki silang
                cell(r, c) { return document.getElementById('c-' + r + '-' + c); },
                move(r, c, d) { const n = this.cell(r + d[0], c + d[1]); if (n) n.focus(); },
                typed(e, r, c) {
                    const v = (e.target.value || '').toUpperCase().slice(-1);
                    e.target.value = v;
                    if (v) this.move(r, c, this.dir === 'across' ? [0, 1] : [1, 0]);
                },
                key(e, r, c) {
                    const m = {ArrowRight: [0, 1], ArrowLeft: [0, -1], ArrowDown: [1, 0], ArrowUp: [-1, 0]}[e.key];
                    if (m) { e.preventDefault(); this.dir = m[0] === 0 ? 'across' : 'down'; this.move(r, c, m); }
                    else if (e.key === 'Backspace' && !e.target.value) { this.move(r, c, this.dir === 'across' ? [0, -1] : [-1, 0]); }
                },
            };
        }
    </script>

    <div x-data="puzzlePlay({ deadline: @js($deadlineMs), now: @js($serverNowMs), tiles: @js($tiles) })" class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <header class="sticky top-[72px] z-30 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-sibaya-wahana-line bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border">
            <div>
                <h1 class="text-lg font-bold text-sibaya-wahana-ink">{{ $puzzle->title }}</h1>
                @if ($puzzle->instructions)<p class="text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($puzzle->instructions, 140) }}</p>@endif
            </div>
            @if ($deadlineMs)
                <div class="text-right">
                    <p role="timer" class="font-mono text-2xl font-extrabold text-sibaya-navy-800"><span class="sr-only">Sisa wektu: </span><span x-text="label"></span></p>
                    <p x-show="remaining !== null && remaining > 0 && remaining <= 30" x-cloak class="text-xs font-semibold text-red-700" role="alert">⚠ Wektu meh entek!</p>
                </div>
            @endif
        </header>

        <form x-ref="form" method="POST" action="{{ route('puzzles.submit', $attempt) }}" @submit="submitting = true" class="mt-6 space-y-5">
            @csrf

            @if ($isScramble)
                @foreach ($play['items'] as $i => $item)
                    <fieldset class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-sibaya-wahana-line">
                        <legend class="px-1 text-sm font-semibold text-sibaya-wahana-ink">Soal {{ $i + 1 }} <span class="font-normal text-slate-500">&middot; {{ $item['points'] }} poin &middot; {{ $item['length'] }} huruf</span></legend>
                        <p class="font-medium">{{ $item['clue'] }}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @for ($b = 0; $b < $item['length']; $b++)
                                <span aria-hidden="true" class="flex h-11 w-11 items-center justify-center rounded-lg border-2 border-sibaya-navy-700 bg-white text-xl font-bold" x-text="letters({{ $i }})[{{ $b }}] || ''"></span>
                            @endfor
                        </div>
                        <p class="sr-only" aria-live="polite" x-text="'Jawaban soal {{ $i + 1 }}: ' + value({{ $i }})"></p>

                        <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Huruf sing kasedhiya kanggo soal {{ $i + 1 }}">
                            <template x-for="(letter, t) in tiles[{{ $i }}]" :key="t">
                                <button type="button" @click="pick({{ $i }}, t)" :disabled="picks[{{ $i }}].includes(t)" :aria-label="'Huruf ' + letter" x-text="letter"
                                        class="h-11 w-11 rounded-lg bg-sibaya-wahana-bg text-xl font-bold text-sibaya-wahana-ink ring-1 ring-sibaya-wahana-line hover:bg-amber-100 disabled:opacity-30"></button>
                            </template>
                        </div>
                        <div class="mt-3 flex gap-2 text-sm">
                            <button type="button" @click="undo({{ $i }})" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold">&larr; Busak siji</button>
                            <button type="button" @click="reset({{ $i }})" class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold">Ulang</button>
                        </div>
                        <input type="hidden" name="items[{{ $i }}]" :value="value({{ $i }})">
                    </fieldset>
                @endforeach
            @else
                <div class="overflow-x-auto rounded-2xl bg-white p-4 shadow-sm ring-1 ring-sibaya-wahana-line">
                    <table class="mx-auto border-collapse">
                        <caption class="sr-only">Kothak teka-teki silang. Gunakake tombol panah kanggo pindhah antar kothak.</caption>
                        <tbody>
                        @for ($r = 0; $r < $play['rows']; $r++)
                            <tr>
                                @for ($c = 0; $c < $play['cols']; $c++)
                                    @php $key = "{$r}-{$c}"; $num = $play['numbers'][$key] ?? null; @endphp
                                    @if (isset($play['active'][$key]))
                                        <td class="relative h-10 w-10 border border-slate-400 bg-white p-0 sm:h-11 sm:w-11">
                                            @if ($num)<span class="pointer-events-none absolute left-0.5 top-0 text-[10px] font-bold text-slate-600" aria-hidden="true">{{ $num }}</span>@endif
                                            <input id="c-{{ $r }}-{{ $c }}" name="cells[{{ $key }}]" maxlength="1" autocomplete="off" autocapitalize="characters"
                                                   aria-label="Baris {{ $r + 1 }}, kolom {{ $c + 1 }}{{ $num ? ', nomer '.$num : '' }}"
                                                   class="h-full w-full border-0 bg-transparent p-0 text-center text-lg font-bold uppercase focus:bg-amber-100 focus:ring-2 focus:ring-sibaya-navy-700"
                                                   @input="typed($event, {{ $r }}, {{ $c }})" @keydown="key($event, {{ $r }}, {{ $c }})">
                                        </td>
                                    @else
                                        <td class="h-10 w-10 bg-sibaya-navy-900 sm:h-11 sm:w-11" aria-hidden="true"></td>
                                    @endif
                                @endfor
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['across' => 'Mendatar', 'down' => 'Menurun'] as $dir => $label)
                        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-sibaya-wahana-line" aria-labelledby="h-{{ $dir }}">
                            <h2 id="h-{{ $dir }}" class="text-lg font-bold text-sibaya-wahana-ink">{{ $label }}</h2>
                            <ol class="mt-2 space-y-2 text-sm">
                                @forelse ($play['clues'][$dir] as $clue)
                                    <li><strong>{{ $clue['number'] }}.</strong> {{ $clue['clue'] }} <span class="text-xs text-slate-500">({{ $clue['length'] }} huruf &middot; {{ $clue['points'] }} poin)</span></li>
                                @empty
                                    <li class="text-slate-500">-</li>
                                @endforelse
                            </ol>
                        </section>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end">
                <button type="submit" :disabled="submitting" class="rounded-full bg-sibaya-wahana-accent px-8 py-3 font-semibold text-white hover:opacity-90 disabled:opacity-60">
                    <span x-text="submitting ? 'Ngirim…' : 'Kirim'"></span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.public>
