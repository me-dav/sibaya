@php
    use App\Support\SafeMarkdown;
    use App\Support\VideoEmbed;
    use Illuminate\Support\Facades\Storage;

    $embed = VideoEmbed::embedUrl($material->video_url);
    $exercisesUrl = Route::has('exercises.index')
        ? route('exercises.index', ['rinengga_type' => $material->rinenggaType?->slug])
        : '#';

    $toc = array_filter([
        'Isi Materi' => '#isi',
        'Tuladha'    => filled($material->examples) ? '#contoh' : null,
        'Audio'      => $material->audio_path ? '#audio' : null,
        'Video'      => $material->video_url ? '#video' : null,
        'Rangkuman'  => filled($material->summary) ? '#rangkuman' : null,
    ]);
@endphp
<x-layouts.public :title="$material->title">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-breadcrumb :items="['Beranda' => route('home'), 'Ruang Piwulang' => route('learning.index'), $material->title => null]" />

        <div class="mt-6 gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_16rem]">
            <article class="min-w-0 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-sibaya-sky-200 sm:p-8">
                <header>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-sibaya-sky-100 px-3 py-1 text-sm font-medium text-sibaya-navy-800">{{ $material->rinenggaType?->name }}</span>
                        @unless ($material->is_published)
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">Draft &ndash; mung katon kanggo admin</span>
                        @endunless
                    </div>
                    <h1 class="mt-3 text-3xl font-extrabold text-sibaya-navy-800 sm:text-4xl">{{ $material->title }}</h1>
                    <p class="mt-3 text-lg leading-relaxed text-sibaya-ink/80">{{ $material->description }}</p>
                </header>

                @if ($material->image_path)
                    <figure class="mt-6">
                        <img src="{{ Storage::disk('public')->url($material->image_path) }}" alt="Gambar materi {{ $material->title }}" class="max-h-96 w-full rounded-2xl object-cover" loading="lazy">
                    </figure>
                @endif

                <section id="isi" class="mt-8 scroll-mt-24" aria-labelledby="h-isi">
                    <h2 id="h-isi" class="text-2xl font-bold text-sibaya-navy-800">Isi Materi</h2>
                    <div class="prose-sibaya mt-3">{{ SafeMarkdown::render($material->content) }}</div>
                </section>

                @if (filled($material->examples))
                    <section id="contoh" class="mt-10 scroll-mt-24" aria-labelledby="h-contoh">
                        <h2 id="h-contoh" class="text-2xl font-bold text-sibaya-navy-800">Tuladha</h2>
                        <div class="prose-sibaya mt-3">{{ SafeMarkdown::render($material->examples) }}</div>
                    </section>
                @endif

                @if ($material->audio_path)
                    <section id="audio" class="mt-10 scroll-mt-24" aria-labelledby="h-audio">
                        <h2 id="h-audio" class="text-2xl font-bold text-sibaya-navy-800">Audio Nyimak</h2>
                        <audio controls controlsList="nodownload" preload="none" oncontextmenu="return false" class="mt-3 w-full">
                            <source src="{{ Storage::disk('public')->url($material->audio_path) }}">
                            Browser sampeyan ora ndhukung pamuter audio.
                        </audio>
                    </section>
                @endif

                @if ($material->video_url)
                    <section id="video" class="mt-10 scroll-mt-24" aria-labelledby="h-video">
                        <h2 id="h-video" class="text-2xl font-bold text-sibaya-navy-800">Video Piwulang</h2>
                        @if ($embed)
                            <div class="mt-3 aspect-video overflow-hidden rounded-2xl bg-slate-900">
                                <iframe src="{{ $embed }}" title="Video piwulang {{ $material->title }}" class="h-full w-full" loading="lazy"
                                        referrerpolicy="strict-origin-when-cross-origin" allow="encrypted-media; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        @else
                            <a href="{{ $material->video_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-sibaya-navy-700 underline">Bukak video ing tab anyar</a>
                        @endif
                    </section>
                @endif

                @if (filled($material->summary))
                    <section id="rangkuman" class="mt-10 scroll-mt-24 rounded-2xl bg-sibaya-gold-soft/20 p-5" aria-labelledby="h-rangkuman">
                        <h2 id="h-rangkuman" class="text-2xl font-bold text-sibaya-navy-800">Rangkuman</h2>
                        <div class="prose-sibaya mt-3">{{ SafeMarkdown::render($material->summary) }}</div>
                    </section>
                @endif

                <footer class="mt-10 flex flex-wrap gap-3 border-t border-sibaya-sky-200 pt-6">
                    <a href="{{ route('learning.index') }}" class="rounded-full border border-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-sibaya-navy-800 hover:bg-sibaya-sky-100">&larr; Bali</a>
                    <a href="{{ $exercisesUrl }}" class="rounded-full bg-sibaya-navy-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sibaya-navy-700">Gladhen {{ $material->rinenggaType?->name }} &rarr;</a>
                </footer>
            </article>

            <aside class="mt-6 space-y-6 lg:sticky lg:top-24 lg:mt-0 lg:self-start">
                <nav aria-label="Daftar isi" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200">
                    <p class="font-bold text-sibaya-navy-800">Daftar Isi</p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($toc as $label => $href)
                            <li><a href="{{ $href }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-sibaya-sky-100">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
                @if ($others->isNotEmpty())
                    <nav aria-label="Materi liyane" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-sibaya-sky-200">
                        <p class="font-bold text-sibaya-navy-800">Materi Liyane</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($others as $other)
                                <li><a href="{{ route('learning.show', $other) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-sibaya-sky-100">{{ $other->rinenggaType->name }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </aside>
        </div>
    </div>
</x-layouts.public>
