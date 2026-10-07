<?php

namespace Database\Seeders;

use App\Models\Puzzle;
use Illuminate\Database\Seeder;

/** Data contoh untuk DEVELOPMENT. Petunjuk diambil dari ringkasan materi Basa Rinengga. */
class PuzzleSeeder extends Seeder
{
    public function run(): void
    {
        $this->make('susun-kata-basa-rinengga', 'Susun Kata: Jinis Basa Rinengga', 'susun_kata', 'mudah', [
            'duration_seconds' => null,
            'items' => [
                ['clue' => 'Unen-unen kang wis gumathok panggonane lan nduweni teges tartamtu.', 'answer' => 'PARIBASAN', 'points' => 10],
                ['clue' => 'Unen-unen kang nduweni teges entar lan biasane nggambarake watak utawa kahanan manungsa.', 'answer' => 'BEBASAN', 'points' => 10],
                ['clue' => 'Unen-unen kang nggunakake pralambang kanggo nggambarake kahanan utawa watak wong.', 'answer' => 'SALOKA', 'points' => 10],
                ['clue' => 'Unen-unen kang ora langsung lan nduweni teges kosok balen utawa ora salugune.', 'answer' => 'SANEPA', 'points' => 10],
            ],
        ]);

        $this->make('tts-basa-rinengga', 'Teka-teki Silang: Basa Rinengga', 'teka_teki_silang', 'sedang', [
            'duration_seconds' => 600,
            'rows' => 9,
            'cols' => 9,
            'words' => [
                ['answer' => 'PARIBASAN', 'clue' => 'Unen-unen kang wis gumathok panggonane lan nduweni teges tartamtu.', 'direction' => 'across', 'row' => 1, 'col' => 1, 'points' => 10],
                ['answer' => 'PEPINDHAN', 'clue' => 'Mbandhingake siji bab karo bab liyane, biasane nganggo tembung kaya, kadya, lir, pindha, umpama.', 'direction' => 'down', 'row' => 1, 'col' => 1, 'points' => 10],
                ['answer' => 'BEBASAN', 'clue' => 'Unen-unen kang nduweni teges entar lan biasane nggambarake watak utawa kahanan manungsa.', 'direction' => 'down', 'row' => 1, 'col' => 5, 'points' => 10],
                ['answer' => 'SALOKA', 'clue' => 'Unen-unen kang nggunakake pralambang kanggo nggambarake kahanan utawa watak wong.', 'direction' => 'down', 'row' => 1, 'col' => 7, 'points' => 10],
            ],
        ]);
    }

    private function make(string $slug, string $title, string $type, string $difficulty, array $configuration): void
    {
        $p = Puzzle::where('slug', $slug)->first() ?? new Puzzle();
        $p->forceFill([
            'title' => $title, 'slug' => $slug, 'puzzle_type' => $type, 'difficulty' => $difficulty,
            'description' => 'Puzzle contoh kanggo development.',
            'instructions' => $type === 'susun_kata'
                ? 'Klik huruf-huruf supaya dadi tembung sing bener miturut pitedah.'
                : 'Isi kothak-kothak nganggo huruf miturut pitedah mendatar lan menurun.',
            'configuration' => $configuration, 'is_published' => true,
        ])->save();
    }
}
