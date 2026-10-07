<?php

namespace App\Services;

use App\Models\Puzzle;
use App\Models\PuzzleAttempt;
use App\Models\User;
use App\Support\Crossword;
use App\Support\Labels;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/** Konfigurasi, permainan, dan penilaian Wahana Teka-teki. Semua penilaian dilakukan di server. */
class PuzzleService
{
    public const GRACE_SECONDS = 3;

    // ---------------------------------------------------------------- admin

    /** Susun konfigurasi jsonb dari data yang sudah divalidasi. */
    public function configurationFrom(string $type, array $d): array
    {
        $duration = filled($d['duration_seconds'] ?? null) ? (int) $d['duration_seconds'] : null;

        if ($type === 'susun_kata') {
            return [
                'duration_seconds' => $duration,
                'items' => collect($d['items'])->map(fn ($i) => [
                    'clue' => trim($i['clue']), 'answer' => $i['answer'], 'points' => (int) $i['points'],
                ])->values()->all(),
            ];
        }

        return [
            'duration_seconds' => $duration,
            'rows' => (int) $d['grid_rows'],
            'cols' => (int) $d['grid_cols'],
            'words' => collect($d['words'])->map(fn ($w) => [
                'answer' => $w['answer'], 'clue' => trim($w['clue']), 'direction' => $w['direction'],
                'row' => (int) $w['row'], 'col' => (int) $w['col'], 'points' => (int) $w['points'],
            ])->values()->all(),
        ];
    }

    public function create(array $d): Puzzle
    {
        $puzzle = new Puzzle();
        $puzzle->forceFill([
            'title' => $d['title'], 'slug' => $d['slug'], 'description' => $d['description'] ?? null,
            'puzzle_type' => $d['puzzle_type'], 'difficulty' => $d['difficulty'] ?? null,
            'instructions' => $d['instructions'] ?? null, 'is_published' => (bool) $d['is_published'],
            'configuration' => $this->configurationFrom($d['puzzle_type'], $d),
        ])->save();

        return $puzzle;
    }

    public function update(Puzzle $puzzle, array $d): Puzzle
    {
        $type = (string) Labels::value($puzzle->puzzle_type);

        $puzzle->forceFill([
            'title' => $d['title'], 'slug' => $d['slug'], 'description' => $d['description'] ?? null,
            'difficulty' => $d['difficulty'] ?? null, 'instructions' => $d['instructions'] ?? null,
            'is_published' => (bool) $d['is_published'],
            'configuration' => $this->configurationFrom($type, $d),
        ])->save();

        return $puzzle;
    }

    /** Puzzle yang sudah punya percobaan tidak boleh dihapus (riwayat siswa). */
    public function canDelete(Puzzle $puzzle): bool
    {
        return ! DB::table('puzzle_attempts')->where('puzzle_id', $puzzle->id)->exists();
    }

    // ---------------------------------------------------------------- bermain

    public function start(User $user, Puzzle $puzzle): PuzzleAttempt
    {
        $attempt = new PuzzleAttempt();
        $attempt->forceFill(['user_id' => $user->id, 'puzzle_id' => $puzzle->id, 'score' => 0])->save();

        return $attempt;
    }

    public function limitSeconds(Puzzle $puzzle): ?int
    {
        $limit = $puzzle->configuration['duration_seconds'] ?? null;

        return $limit ? (int) $limit : null;
    }

    public function deadline(PuzzleAttempt $attempt, Puzzle $puzzle): ?Carbon
    {
        $limit = $this->limitSeconds($puzzle);

        return $limit ? Carbon::parse($attempt->created_at)->addSeconds($limit) : null;
    }

    /** Data untuk halaman bermain. Tidak memuat kunci jawaban. */
    public function playData(Puzzle $puzzle, PuzzleAttempt $attempt): array
    {
        $cfg = $puzzle->configuration;

        if (Labels::value($puzzle->puzzle_type) === 'susun_kata') {
            $items = [];
            foreach ($cfg['items'] as $i => $item) {
                $items[] = [
                    'clue' => $item['clue'], 'length' => mb_strlen($item['answer']), 'points' => (int) $item['points'],
                    'tiles' => self::scramble($item['answer'], $attempt->id * 1000 + $i),
                ];
            }

            return ['type' => 'susun_kata', 'items' => $items];
        }

        return ['type' => 'teka_teki_silang'] + Crossword::publicLayout(Crossword::layout($cfg));
    }

    /** Acak huruf secara deterministik per percobaan (muat ulang tidak mengubah susunan) dan tidak sama dengan kata asli. */
    public static function scramble(string $word, int $seed): array
    {
        $letters = mb_str_split($word);

        if (count(array_unique($letters)) < 2) {
            return $letters;
        }

        for ($try = 0; $try < 25; $try++) {
            $shuffled = (new Randomizer(new Mt19937($seed + $try)))->shuffleArray($letters);
            if ($shuffled !== $letters) {
                return $shuffled;
            }
        }

        return array_reverse($letters);
    }

    /**
     * Kirim jawaban dan nilai. Idempotent. Pada puzzle berwaktu, kiriman setelah batas (+toleransi) bernilai 0.
     *
     * @return array{attempt: PuzzleAttempt, review: ?array, late: bool}
     */
    public function submit(PuzzleAttempt $attempt, array $answers): array
    {
        return DB::transaction(function () use ($attempt, $answers) {
            $attempt = PuzzleAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();

            if ($attempt->completed_at !== null) {
                return ['attempt' => $attempt, 'review' => null, 'late' => false];
            }

            $puzzle = Puzzle::findOrFail($attempt->puzzle_id);
            $limit = $this->limitSeconds($puzzle);
            $now = now();
            $started = Carbon::parse($attempt->created_at);
            $late = $limit !== null && $now->gt($started->copy()->addSeconds($limit + self::GRACE_SECONDS));

            $review = null;
            $score = 0.0;

            if (! $late) {
                $review = Labels::value($puzzle->puzzle_type) === 'susun_kata'
                    ? $this->gradeScramble($puzzle->configuration, $answers['items'] ?? [])
                    : $this->gradeCrossword($puzzle->configuration, $answers['cells'] ?? []);

                $max = array_sum(array_column($review, 'points'));
                $earned = array_sum(array_column($review, 'earned'));
                $score = $max > 0 ? round($earned / $max * 100, 2) : 0.0;
            }

            $elapsed = max(0, $now->getTimestamp() - $started->getTimestamp());

            $attempt->forceFill([
                'score'            => $score,
                'duration_seconds' => $limit !== null ? min($limit, $elapsed) : $elapsed,
                'completed_at'     => $now,
            ])->save();

            return ['attempt' => $attempt, 'review' => $review, 'late' => $late];
        });
    }

    private function normalize(?string $s): string
    {
        return mb_strtoupper(preg_replace('/\s+/u', '', trim((string) $s)));
    }

    private function gradeScramble(array $cfg, array $given): array
    {
        $review = [];

        foreach ($cfg['items'] as $i => $item) {
            $answer = $this->normalize($given[$i] ?? '');
            $ok = $answer !== '' && $answer === $item['answer'];

            $review[] = [
                'label' => 'Soal '.($i + 1), 'clue' => $item['clue'], 'given' => $answer,
                'correct' => $ok, 'points' => (int) $item['points'], 'earned' => $ok ? (int) $item['points'] : 0,
            ];
        }

        return $review;
    }

    private function gradeCrossword(array $cfg, array $cells): array
    {
        $layout = Crossword::layout($cfg);
        $review = [];

        foreach ($layout['words'] as $w) {
            $given = '';
            foreach ($w['cells'] as [$r, $c]) {
                $given .= $this->normalize($cells["{$r}-{$c}"] ?? '') ?: '·';
            }

            $ok = $given === $w['answer'];
            $review[] = [
                'label' => $w['number'].' '.($w['direction'] === 'across' ? 'Mendatar' : 'Menurun'), 'clue' => $w['clue'], 'given' => $given,
                'correct' => $ok, 'points' => (int) $w['points'], 'earned' => $ok ? (int) $w['points'] : 0,
            ];
        }

        return $review;
    }
}
