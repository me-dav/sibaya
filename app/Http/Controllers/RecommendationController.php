<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RecommendationController extends Controller
{
    /** Tandai rekomendasi sudah dibaca, lalu buka materinya. */
    public function open(Recommendation $recommendation): RedirectResponse
    {
        Gate::authorize('view', $recommendation);

        if (! $recommendation->is_read) {
            $recommendation->forceFill(['is_read' => true])->save();
        }

        $material = Material::with('rinenggaType')->find($recommendation->material_id);

        if (! $material || ! $material->is_published || ! $material->rinenggaType?->is_published) {
            return redirect()->route('learning.index')->with('error', 'Materi iki durung kasedhiya.');
        }

        return redirect()->route('learning.show', $material);
    }
}
