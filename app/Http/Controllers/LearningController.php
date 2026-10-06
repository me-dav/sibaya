<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\RinenggaType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class LearningController extends Controller
{
    public function index(): View
    {
        $types = RinenggaType::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->with(['materials' => fn ($q) => $q->where('is_published', true)->orderBy('id')])
            ->get();

        $published = $types->flatMap->materials;

        // Materi tujuan untuk kartu pintasan (bagian artikel yang tersimpan di kolom materi).
        $first = $published->first();
        $shortcuts = [
            'contoh'    => $published->first(fn ($m) => filled($m->examples)) ?? $first,
            'teks'      => $first,
            'audio'     => $published->first(fn ($m) => filled($m->audio_path)),
            'video'     => $published->first(fn ($m) => filled($m->video_url)),
            'rangkuman' => $published->first(fn ($m) => filled($m->summary)),
        ];

        return view('learning.index', compact('types', 'shortcuts'));
    }

    public function show(Material $material): View
    {
        // 404 (bukan 403) agar keberadaan draft tidak terbongkar.
        abort_unless(Gate::allows('view', $material), 404);

        $material->load('rinenggaType');

        $others = Material::query()
            ->where('is_published', true)
            ->where('id', '!=', $material->id)
            ->whereHas('rinenggaType', fn ($q) => $q->where('is_published', true))
            ->with('rinenggaType')
            ->get()
            ->sortBy(fn ($m) => $m->rinenggaType->sort_order)
            ->values();

        return view('learning.show', compact('material', 'others'));
    }
}
