@php
    use App\Support\PuzzleRules;
    $input = 'mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700';
    $isEdit = $puzzle->exists;
    $cfg = $isEdit ? $puzzle->configuration : [];
    $items = array_values(old('items', $cfg['items'] ?? [['clue' => '', 'answer' => '', 'points' => 10]]));
    $words = array_values(old('words', $cfg['words'] ?? [['answer' => '', 'clue' => '', 'direction' => 'across', 'row' => 1, 'col' => 1, 'points' => 10]]));
    $gridRows = (int) old('grid_rows', $cfg['rows'] ?? 10);
    $gridCols = (int) old('grid_cols', $cfg['cols'] ?? 10);
    $listErrors = fn ($prefix) => collect($errors->keys())->filter(fn ($k) => str_starts_with($k, $prefix))->flatMap(fn ($k) => $errors->get($k))->unique();
@endphp
<script>
    function puzzleForm(init) {
        return {
            type: init.type, items: init.items, words: init.words, rows: init.rows, cols: init.cols,
            get grid() {
                const rows = +this.rows || 0, cols = +this.cols || 0;
                if (rows < 1 || cols < 1 || rows > 25 || cols > 25) return {cells: []};
                const cells = Array.from({length: rows}, () => Array.from({length: cols}, () => ({letter: null, num: null, bad: false})));
                const starts = [];
                this.words.forEach((w) => {
                    const a = String(w.answer || '').replace(/\s+/g, '').toUpperCase();
                    const r = (+w.row || 1) - 1, c = (+w.col || 1) - 1, down = w.direction === 'down';
                    [...a].forEach((ch, k) => {
                        const rr = r + (down ? k : 0), cc = c + (down ? 0 : k);
                        if (rr < 0 || cc < 0 || rr >= rows || cc >= cols) return;
                        const cell = cells[rr][cc];
                        if (cell.letter && cell.letter !== ch) cell.bad = true; else cell.letter = ch;
                    });
                    if (a.length && r >= 0 && c >= 0 && r < rows && c < cols) starts.push([r, c]);
                });
                starts.sort((x, y) => x[0] - y[0] || x[1] - y[1]);
                let n = 0;
                starts.forEach(([r, c]) => { if (!cells[r][c].num) cells[r][c].num = ++n; });
                return {cells};
            },
        };
    }
</script>
<form method="POST" action="{{ $action }}" x-data="puzzleForm({ type: @js($type), items: @js($items), words: @js($words), rows: @js($gridRows), cols: @js($gridCols) })"
      class="mt-6 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    @if ($errors->any())<div role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">Ana isian sing durung bener. Mangga dicek maneh.</div>@endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div><label for="title" class="block text-sm font-medium">Judul <span class="text-red-600">*</span></label>
            <input id="title" name="title" value="{{ old('title', $puzzle->title) }}" required class="{{ $input }}"><x-input-error :messages="$errors->get('title')" class="mt-1" /></div>
        <div><label for="slug" class="block text-sm font-medium">Slug (URL)</label>
            <input id="slug" name="slug" value="{{ old('slug', $puzzle->slug) }}" class="{{ $input }}"><x-input-error :messages="$errors->get('slug')" class="mt-1" /></div>

        <div>
            @if ($isEdit)
                <p class="text-sm"><span class="text-slate-500">Jenis puzzle:</span> <strong>{{ PuzzleRules::types()[$type] ?? $type }}</strong>
                    <span class="text-xs text-slate-500">(ora bisa diowahi)</span></p>
            @else
                <label for="puzzle_type" class="block text-sm font-medium">Jenis puzzle <span class="text-red-600">*</span></label>
                <select id="puzzle_type" name="puzzle_type" x-model="type" class="{{ $input }}">
                    @foreach (PuzzleRules::types() as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select><x-input-error :messages="$errors->get('puzzle_type')" class="mt-1" />
            @endif
        </div>
        <div><label for="difficulty" class="block text-sm font-medium">Tingkat kesulitan</label>
            <select id="difficulty" name="difficulty" class="{{ $input }}"><option value="">-</option>
                @foreach (PuzzleRules::difficulties() as $v => $l)<option value="{{ $v }}" @selected(old('difficulty', $puzzle->difficulty) === $v)>{{ $l }}</option>@endforeach</select>
            <x-input-error :messages="$errors->get('difficulty')" class="mt-1" /></div>
        <div><label for="duration_seconds" class="block text-sm font-medium">Batas wektu (detik, kosong = bebas)</label>
            <input id="duration_seconds" type="number" min="30" max="7200" name="duration_seconds" value="{{ old('duration_seconds', $cfg['duration_seconds'] ?? '') }}" class="{{ $input }}">
            <x-input-error :messages="$errors->get('duration_seconds')" class="mt-1" /></div>
    </div>

    <div><label for="description" class="block text-sm font-medium">Deskripsi</label>
        <textarea id="description" name="description" rows="2" class="{{ $input }}">{{ old('description', $puzzle->description) }}</textarea></div>
    <div><label for="instructions" class="block text-sm font-medium">Petunjuk main</label>
        <textarea id="instructions" name="instructions" rows="3" class="{{ $input }}">{{ old('instructions', $puzzle->instructions) }}</textarea></div>

    {{-- Susun kata --}}
    <fieldset x-show="type === 'susun_kata'" x-cloak class="space-y-3">
        <legend class="text-sm font-semibold text-sibaya-navy-800">Soal susun kata</legend>
        <p class="text-xs text-slate-500">Jawaban mung huruf (2-20), tanpa spasi. Huruf diacak otomatis saben percobaan.</p>
        <template x-for="(item, i) in items" :key="i">
            <div class="grid gap-3 rounded-lg bg-slate-50 p-3 sm:grid-cols-12">
                <div class="sm:col-span-6"><label class="block text-xs font-medium" :for="'it-clue-' + i">Pitedah</label>
                    <input :id="'it-clue-' + i" :name="`items[${i}][clue]`" x-model="item.clue" class="{{ $input }}"></div>
                <div class="sm:col-span-3"><label class="block text-xs font-medium" :for="'it-ans-' + i">Jawaban</label>
                    <input :id="'it-ans-' + i" :name="`items[${i}][answer]`" x-model="item.answer" class="{{ $input }} uppercase"></div>
                <div class="sm:col-span-2"><label class="block text-xs font-medium" :for="'it-pt-' + i">Poin</label>
                    <input :id="'it-pt-' + i" type="number" min="1" :name="`items[${i}][points]`" x-model="item.points" class="{{ $input }}"></div>
                <div class="flex items-end sm:col-span-1"><button type="button" x-show="items.length > 1" @click="items.splice(i, 1)" class="text-sm text-red-700 hover:underline" :aria-label="'Busak soal ' + (i + 1)">Busak</button></div>
            </div>
        </template>
        <button type="button" @click="items.push({clue: '', answer: '', points: 10})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold">+ Tambah soal</button>
        @foreach ($listErrors('items') as $m)<p class="text-sm text-red-600">{{ $m }}</p>@endforeach
    </fieldset>

    {{-- Teka-teki silang --}}
    <fieldset x-show="type === 'teka_teki_silang'" x-cloak class="space-y-3">
        <legend class="text-sm font-semibold text-sibaya-navy-800">Teka-teki silang</legend>
        <div class="grid max-w-md gap-4 sm:grid-cols-2">
            <div><label for="grid_rows" class="block text-xs font-medium">Jumlah baris (3-25)</label>
                <input id="grid_rows" type="number" min="3" max="25" name="grid_rows" x-model="rows" class="{{ $input }}"></div>
            <div><label for="grid_cols" class="block text-xs font-medium">Jumlah kolom (3-25)</label>
                <input id="grid_cols" type="number" min="3" max="25" name="grid_cols" x-model="cols" class="{{ $input }}"></div>
        </div>
        <p class="text-xs text-slate-500">Baris dan kolom diwiwiti saka 1 (pojok kiwa ndhuwur). Tembung sing bersilangan kudu nuduhake huruf sing padha.</p>

        <template x-for="(w, i) in words" :key="i">
            <div class="grid gap-3 rounded-lg bg-slate-50 p-3 sm:grid-cols-12">
                <div class="sm:col-span-3"><label class="block text-xs font-medium" :for="'w-ans-' + i">Jawaban</label>
                    <input :id="'w-ans-' + i" :name="`words[${i}][answer]`" x-model="w.answer" class="{{ $input }} uppercase"></div>
                <div class="sm:col-span-4"><label class="block text-xs font-medium" :for="'w-clue-' + i">Pitedah</label>
                    <input :id="'w-clue-' + i" :name="`words[${i}][clue]`" x-model="w.clue" class="{{ $input }}"></div>
                <div class="sm:col-span-2"><label class="block text-xs font-medium" :for="'w-dir-' + i">Arah</label>
                    <select :id="'w-dir-' + i" :name="`words[${i}][direction]`" x-model="w.direction" class="{{ $input }}"><option value="across">Mendatar</option><option value="down">Menurun</option></select></div>
                <div class="sm:col-span-1"><label class="block text-xs font-medium" :for="'w-row-' + i">Baris</label>
                    <input :id="'w-row-' + i" type="number" min="1" :name="`words[${i}][row]`" x-model="w.row" class="{{ $input }}"></div>
                <div class="sm:col-span-1"><label class="block text-xs font-medium" :for="'w-col-' + i">Kolom</label>
                    <input :id="'w-col-' + i" type="number" min="1" :name="`words[${i}][col]`" x-model="w.col" class="{{ $input }}"></div>
                <div class="sm:col-span-1"><label class="block text-xs font-medium" :for="'w-pt-' + i">Poin</label>
                    <input :id="'w-pt-' + i" type="number" min="1" :name="`words[${i}][points]`" x-model="w.points" class="{{ $input }}"></div>
                <div class="sm:col-span-12 text-right"><button type="button" x-show="words.length > 1" @click="words.splice(i, 1)" class="text-sm text-red-700 hover:underline" :aria-label="'Busak tembung ' + (i + 1)">Busak tembung iki</button></div>
            </div>
        </template>
        <button type="button" @click="words.push({answer: '', clue: '', direction: 'across', row: 1, col: 1, points: 10})" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold">+ Tambah tembung</button>
        @foreach ($listErrors('words') as $m)<p class="text-sm text-red-600">{{ $m }}</p>@endforeach
        @foreach ($listErrors('grid_') as $m)<p class="text-sm text-red-600">{{ $m }}</p>@endforeach

        <div>
            <p class="text-sm font-semibold text-sibaya-navy-800">Pratinjau kothak <span class="text-xs font-normal text-slate-500">(abang = huruf bentrok)</span></p>
            <div class="mt-2 overflow-x-auto rounded-lg bg-slate-100 p-3">
                <table class="border-collapse">
                    <caption class="sr-only">Pratinjau kothak teka-teki silang</caption>
                    <tbody>
                        <template x-for="(row, r) in grid.cells" :key="r">
                            <tr>
                                <template x-for="(cell, c) in row" :key="c">
                                    <td class="relative h-7 w-7 border text-center text-sm font-bold"
                                        :class="cell.letter ? (cell.bad ? 'border-red-500 bg-red-100 text-red-700' : 'border-slate-400 bg-white') : 'border-slate-200 bg-slate-300'">
                                        <span x-show="cell.num" class="absolute left-0.5 top-0 text-[8px] font-bold text-slate-600" x-text="cell.num"></span>
                                        <span x-text="cell.letter || ''"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_published" value="0">
        <input id="is_published" type="checkbox" name="is_published" value="1" @checked(old('is_published', $puzzle->is_published)) class="rounded border-slate-300">
        <label for="is_published" class="text-sm font-medium">Terbitake (katon kanggo siswa)</label>
    </div>

    <div class="flex gap-3 border-t border-slate-200 pt-6">
        <button type="submit" class="rounded-lg bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Simpen</button>
        <a href="{{ route('admin.puzzles.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold">Batal</a>
    </div>
</form>
