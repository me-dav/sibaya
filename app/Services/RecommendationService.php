<?php

namespace App\Services;

use App\Models\EvaluationAttempt;
use App\Models\Material;
use App\Models\Recommendation;
use App\Models\RinenggaType;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rekomendasi materi berdasarkan nilai terendah per jenis Basa Rinengga (CLAUDE.md bagian 17).
 * Tidak ada data acak: tanpa data kategori yang cukup, tidak ada rekomendasi khusus yang dibuat.
 *
 * Jenis Basa Rinengga sebuah soal diambil dari:
 *   1. questions.metadata.rinengga_type_id (soal mandiri evaluasi), atau
 *   2. exercises.rinengga_type_id (soal yang berasal dari latihan).
 */
class RecommendationService
{
    // ---------------------------------------------------------------- perhitungan

    /**
     * Nilai per jenis untuk satu attempt.
     *
     * @return Collection<int, array{type_id: int, name: string, sort_order: int, earned: float, max: float, score: float}>
     */
    public function categoryScores(EvaluationAttempt $attempt): Collection
    {
        $rows = DB::table('evaluation_answers as ea')
            ->join('questions as q', 'q.id', '=', 'ea.question_id')
            ->leftJoin('exercises as ex', 'ex.id', '=', 'q.exercise_id')
            ->where('ea.evaluation_attempt_id', $attempt->id)
            ->get(['q.points as points', 'q.metadata as metadata', 'ex.rinengga_type_id as exercise_type_id', 'ea.points_earned as earned']);

        $groups = [];
        foreach ($rows as $row) {
            $meta = is_string($row->metadata) ? (json_decode($row->metadata, true) ?: []) : (array) $row->metadata;
            $typeId = $meta['rinengga_type_id'] ?? $row->exercise_type_id;

            if (! $typeId) {
                continue; // soal tanpa kategori tidak ikut dihitung
            }

            $groups[$typeId]['earned'] = ($groups[$typeId]['earned'] ?? 0) + (float) $row->earned;
            $groups[$typeId]['max'] = ($groups[$typeId]['max'] ?? 0) + (float) $row->points;
        }

        $types = RinenggaType::whereIn('id', array_keys($groups))->get()->keyBy('id');

        return collect($groups)
            ->filter(fn ($g, $id) => $g['max'] > 0 && $types->has($id))
            ->map(fn ($g, $id) => [
                'type_id'    => (int) $id,
                'name'       => $types[$id]->name,
                'sort_order' => (int) $types[$id]->sort_order,
                'earned'     => $g['earned'],
                'max'        => $g['max'],
                'score'      => round($g['earned'] / $g['max'] * 100, 1),
            ])
            ->sortBy('sort_order')->values();
    }

    // ---------------------------------------------------------------- pembuatan

    /** Buat rekomendasi untuk kategori dengan nilai terendah (semua yang seri). Idempotent. */
    public function generateForAttempt(EvaluationAttempt $attempt): Collection
    {
        $scores = $this->categoryScores($attempt);

        if ($scores->isEmpty() || $scores->min('score') >= 100) {
            return collect(); // data belum cukup, atau semua kategori sudah sempurna
        }

        $lowest = $scores->min('score');
        $created = collect();

        foreach ($scores->filter(fn ($s) => $s['score'] == $lowest) as $s) {
            $type = RinenggaType::find($s['type_id']);
            $material = $type?->is_published
                ? Material::where('rinengga_type_id', $type->id)->where('is_published', true)->orderBy('id')->first()
                : null;

            if (! $material) {
                continue; // tidak membuat rekomendasi ke materi yang tidak ada / belum terbit
            }

            $rec = Recommendation::where('user_id', $attempt->user_id)
                ->where('source_evaluation_attempt_id', $attempt->id)
                ->where('material_id', $material->id)->first();

            if (! $rec) {
                $score = number_format($s['score'], 1, ',', '.');
                $rec = new Recommendation();
                $rec->forceFill([
                    'user_id'                      => $attempt->user_id,
                    'material_id'                  => $material->id,
                    'source_evaluation_attempt_id' => $attempt->id,
                    'reason'                       => "Nilai paling rendah pada materi {$s['name']} ({$score}). Disarankan mempelajari ulang materi {$s['name']}.",
                    'is_read'                      => false,
                ])->save();
            }

            $created->push($rec);
        }

        return $created;
    }

    // ---------------------------------------------------------------- tampilan

    /** Rekomendasi terbaru siswa (tanpa duplikat materi), atau pesan alasan jika belum ada. */
    public function summary(User $user): array
    {
        $recs = Recommendation::where('user_id', $user->id)->latest('id')->limit(30)->get()->unique('material_id')->take(5);
        $items = $this->items($recs);

        if ($items) {
            return $this->payload('ok', null, $items);
        }

        $latest = EvaluationAttempt::where('user_id', $user->id)->whereIn('status', ['completed', 'expired'])->latest('id')->first();

        return $this->emptyState($latest);
    }

    /** Rekomendasi yang lahir dari satu attempt. */
    public function forAttempt(EvaluationAttempt $attempt): array
    {
        $recs = Recommendation::where('user_id', $attempt->user_id)->where('source_evaluation_attempt_id', $attempt->id)->orderBy('id')->get();
        $items = $this->items($recs);

        return $items ? $this->payload('ok', null, $items) : $this->emptyState($attempt);
    }

    private function emptyState(?EvaluationAttempt $attempt): array
    {
        if (! $attempt) {
            return $this->payload('none_yet', 'Rekomendasi bakal katon sawise kowe ngerjakake evaluasi.');
        }

        $scores = $this->categoryScores($attempt);

        if ($scores->isEmpty()) {
            return $this->payload('insufficient',
                'Soal evaluasi durung dikelompokake miturut jinis Basa Rinengga, mula rekomendasi khusus durung bisa digawe. '
                .'Saran umum: waca maneh materi ing Ruang Piwulang.');
        }

        if ($scores->min('score') >= 100) {
            return $this->payload('all_good', 'Mantap! Nilai kabeh jinis Basa Rinengga wis sampurna, ora ana materi sing kudu diulang.');
        }

        return $this->payload('no_material', 'Materi kanggo jinis sing paling lemah durung diterbitake. Mangga waca materi liyane ing Ruang Piwulang.');
    }

    private function payload(string $state, ?string $message, array $items = []): array
    {
        return [
            'state'   => $state,
            'message' => $message,
            'items'   => $items,
            'unread'  => count(array_filter($items, fn ($i) => ! $i['is_read'])),
        ];
    }

    private function items(Collection $recs): array
    {
        $materials = Material::whereIn('id', $recs->pluck('material_id'))->with('rinenggaType')->get()->keyBy('id');

        return $recs->map(function ($r) use ($materials) {
            $m = $materials->get($r->material_id);

            return ($m && $m->is_published && $m->rinenggaType?->is_published) ? [
                'id'         => $r->id,
                'reason'     => $r->reason,
                'is_read'    => (bool) $r->is_read,
                'created_at' => $r->created_at,
                'material'   => $m,
            ] : null;
        })->filter()->values()->all();
    }
}
