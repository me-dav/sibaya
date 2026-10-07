<?php

namespace App\Support;

class PuzzleRules
{
    public static function types(): array
    {
        return ['teka_teki_silang' => 'Teka-teki Silang', 'susun_kata' => 'Puzzle Susun Kata'];
    }

    public static function difficulties(): array
    {
        return ['mudah' => 'Gampang', 'sedang' => 'Sedheng', 'sulit' => 'Angel'];
    }
}
