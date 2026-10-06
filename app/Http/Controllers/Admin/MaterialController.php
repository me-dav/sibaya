<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\RinenggaType;
use App\Services\MaterialService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    public function __construct(private readonly MaterialService $materials)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Material::class);

        $q = trim((string) $request->query('q', ''));

        $materials = Material::query()
            ->with('rinenggaType')
            ->when($q !== '', fn ($query) => $query->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($q).'%']))
            ->when($request->filled('type'), fn ($query) => $query->where('rinengga_type_id', $request->integer('type')))
            ->when(in_array($request->query('status'), ['published', 'draft'], true),
                fn ($query) => $query->where('is_published', $request->query('status') === 'published'))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.materials.index', [
            'materials' => $materials,
            'types'     => RinenggaType::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Material::class);

        return view('admin.materials.create', [
            'types' => RinenggaType::orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $this->materials->create(
            $request->safe()->except(['image', 'audio', 'remove_image', 'remove_audio']),
            $request->file('image'),
            $request->file('audio'),
        );

        return redirect()->route('admin.materials.index')->with('status', 'Materi kasil disimpen.');
    }

    public function edit(Material $material): View
    {
        Gate::authorize('update', $material);

        return view('admin.materials.edit', [
            'material' => $material,
            'types'    => RinenggaType::orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateMaterialRequest $request, Material $material): RedirectResponse
    {
        $this->materials->update(
            $material,
            $request->safe()->except(['image', 'audio', 'remove_image', 'remove_audio']),
            $request->file('image'),
            $request->file('audio'),
            $request->boolean('remove_image'),
            $request->boolean('remove_audio'),
        );

        return redirect()->route('admin.materials.index')->with('status', 'Materi kasil dianyari.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        Gate::authorize('delete', $material);

        if (! $this->materials->canDelete($material)) {
            return back()->with('error', 'Materi iki wis dadi dhasar rekomendasi siswa lan ora bisa dibusak. Ubah dadi Draft wae.');
        }

        $this->materials->delete($material);

        return redirect()->route('admin.materials.index')->with('status', 'Materi kasil dibusak.');
    }
}
