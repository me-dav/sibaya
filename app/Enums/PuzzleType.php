<?php

namespace App\Enums;

enum PuzzleType: string
{
    case TEKA_TEKI_SILANG = 'teka_teki_silang';
    case SUSUN_KATA = 'susun_kata';

    public function label(): string
    {
        return match ($this) {
            self::TEKA_TEKI_SILANG => 'Teka-teki Silang',
            self::SUSUN_KATA       => 'Puzzle Susun Kata',
        };
    }
}
