@php use App\Support\Labels; $total = $questions->count(); @endphp
<x-layouts.public :title="$evaluation->title">
    <script>
        function evaluationAttempt(cfg) {
            return {
                remaining: 0, offset: cfg.now - Date.now(), submitting: false, expired: false,
                saveState: '', answered: Object.assign({}, cfg.initial),
                init() { this.tick(); setInterval(() => this.tick(), 1000); },
                tick() {
                    this.remaining = Math.max(0, Math.floor((cfg.deadline - (Date.now() + this.offset)) / 1000));
                    if (this.remaining === 0 && !this.submitting) {
                        this.submitting = true; this.expired = true;
                        this.$nextTick(() => this.$refs.form.requestSubmit());
                    }
                },
                get label() {
                    const m = Math.floor(this.remaining / 60), s = this.remaining % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
                get answeredCount() { return Object.values(this.answered).filter(Boolean).length; },
                async save(qid, value) {
                    this.answered[qid] = String(value).trim() !== '';
                    this.saveState = 'nyimpen';
                    try {
                        const r = await fetch(cfg.saveUrl, {
                            method: 'PUT',
                            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf},
                            body: JSON.stringify({answers: {[qid]: value}}),
                        });
                        if (r.status === 409) { window.location.reload(); return; }
                        if (!r.ok) throw new Error('save failed');
                        this.saveState = 'kasimpen';
                    } catch (e) { this.saveState = 'gagal'; }
                },
                confirmSubmit(e) {
                    if (this.expired) return;
                    const left = cfg.total - this.answeredCount;
                    if (left > 0 && !confirm('Isih ' + left + ' soal durung kejawab. Kirim saiki?')) { e.preventDefault(); return; }
                    this.submitting = true;
                },
            };
        }
    </script>

    <div x-data="evaluationAttempt({
            deadline: {{ $deadlineMs }}, now: {{ $serverNowMs }}, total: {{ $total }},
            saveUrl: @js(route('evaluations.attempts.answers', $attempt)), csrf: @js(csrf_token()),
            initial: @js($saved) })"
         class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        <header class="sticky top-[72px] z-30 -mx-4 flex flex-wrap items-center justify-between gap-3 border-b border-sibaya-sky-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border">
            <div>
                <h1 class="text-lg font-bold text-sibaya-navy-800">{{ $evaluation->title }}</h1>
                <p class="text-xs text-slate-600">Kejawab <span x-text="answeredCount"></span> / {{ $total }}
                    <span class="ml-2" aria-live="polite" x-text="saveState === 'nyimpen' ? 'Nyimpen…' : (saveState === 'kasimpen' ? '✓ Kasimpen' : (saveState === 'gagal' ? '⚠ Gagal nyimpen, priksa sambungan' : ''))"></span></p>
            </div>
            <div class="text-right">
                <p role="timer" class="font-mono text-2xl font-extrabold text-sibaya-navy-800"><span class="sr-only">Sisa wektu: </span><span x-text="label"></span></p>
                <p x-show="remaining > 0 && remaining <= 300" x-cloak class="text-xs font-semibold text-red-700" role="alert">⚠ Wektu meh entek!</p>
            </div>
        </header>

        <nav aria-label="Nomor soal" class="mt-4 flex flex-wrap gap-2">
            @foreach ($questions as $q)
                <a href="#soal-{{ $loop->iteration }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-semibold ring-1 ring-slate-300 hover:bg-sibaya-sky-100"
                   :class="answered[{{ $q->id }}] ? 'bg-sibaya-navy-800 text-white ring-sibaya-navy-800' : 'bg-white'">
                    {{ $loop->iteration }}<span class="sr-only" x-text="answered[{{ $q->id }}] ? ' (wis kejawab)' : ' (durung kejawab)'"></span>
                </a>
            @endforeach
        </nav>

        <form x-ref="form" method="POST" action="{{ route('evaluations.submit', $attempt) }}" @submit="confirmSubmit($event)" class="mt-6 space-y-5">
            @csrf
            @foreach ($questions as $q)
                @php $row = $answers->get($q->id); $mc = Labels::value($q->question_type) === 'multiple_choice'; @endphp
                <fieldset id="soal-{{ $loop->iteration }}" class="scroll-mt-40 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-sibaya-sky-200">
                    <legend class="px-1 text-sm font-semibold text-sibaya-navy-800">Soal {{ $loop->iteration }} <span class="font-normal text-slate-500">&middot; {{ $q->points }} poin</span></legend>
                    <p class="whitespace-pre-line font-medium">{{ $q->question_text }}</p>

                    @if ($mc)
                        <div class="mt-3 space-y-2">
                            @foreach ($q->options as $o)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg px-3 py-2 ring-1 ring-slate-200 hover:bg-sibaya-sky-100">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $o->id }}" class="mt-1"
                                           @checked($row?->selected_option_id == $o->id) @change="save({{ $q->id }}, $event.target.value)">
                                    <span><strong>{{ $o->option_key }}.</strong> {{ $o->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <label for="jawab-{{ $q->id }}" class="sr-only">Jawaban soal {{ $loop->iteration }}</label>
                        <textarea id="jawab-{{ $q->id }}" name="answers[{{ $q->id }}]" rows="3" maxlength="2000" placeholder="Tulis jawabanmu ing kene"
                                  class="mt-3 w-full rounded-lg border-slate-300 text-sm focus:border-sibaya-navy-700 focus:ring-sibaya-navy-700"
                                  @input.debounce.700ms="save({{ $q->id }}, $event.target.value)">{{ $row?->answer_text }}</textarea>
                    @endif
                </fieldset>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" :disabled="submitting" class="rounded-full bg-sibaya-navy-800 px-8 py-3 font-semibold text-white hover:bg-sibaya-navy-700 disabled:opacity-60">
                    <span x-text="submitting ? 'Ngirim…' : 'Kirim'"></span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.public>
