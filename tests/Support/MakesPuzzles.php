<?php

namespace Tests\Support;

use App\Models\Puzzle;
use App\Support\Crossword;

trait MakesPuzzles
{
    protected function makePuzzle(string $type, array $configuration, array $attrs = []): Puzzle
    {
        $p = new Puzzle();
        $p->forceFill(array_merge([
            'title' => 'Puzzle '.uniqid(), 'slug' => 'puzzle-'.uniqid(), 'puzzle_type' => $type,
            'difficulty' => 'mudah', 'configuration' => $configuration, 'is_published' => true,
        ], $attrs))->save();

        return $p;
    }

    protected function scrambleConfig(?int $duration = null): array
    {
        return ['duration_seconds' => $duration, 'items' => [
            ['clue' => 'Unen-unen kang ora langsung.', 'answer' => 'SANEPA', 'points' => 10],
            ['clue' => 'Unen-unen nganggo pralambang.', 'answer' => 'SALOKA', 'points' => 10],
            ['clue' => 'Unen-unen kang nduweni teges entar.', 'answer' => 'BEBASAN', 'points' => 10],
        ]];
    }

    protected function crosswordConfig(?int $duration = null): array
    {
        return ['duration_seconds' => $duration, 'rows' => 6, 'cols' => 6, 'words' => [
            ['answer' => 'SALOKA', 'clue' => 'Pitedah mendatar.', 'direction' => 'across', 'row' => 1, 'col' => 1, 'points' => 10],
            ['answer' => 'SANEPA', 'clue' => 'Pitedah menurun.', 'direction' => 'down', 'row' => 1, 'col' => 1, 'points' => 10],
        ]];
    }

    /** Isi kotak untuk kata-kata tertentu pada konfigurasi silang. */
    protected function cellsFor(array $config, array $answers): array
    {
        $cells = [];
        foreach (Crossword::layout($config)['words'] as $w) {
            if (in_array($w['answer'], $answers, true)) {
                foreach ($w['cells'] as $k => [$r, $c]) {
                    $cells["{$r}-{$c}"] = mb_substr($w['answer'], $k, 1);
                }
            }
        }

        return $cells;
    }
}
