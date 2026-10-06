<?php

namespace App\Enums;

enum EvaluationType: string
{
    case UTS = 'uts';
    case UAS = 'uas';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
