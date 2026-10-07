<?php

namespace App\Support;

/** Aturan tipe evaluasi: UTS = full pilihan ganda, UAS = isian (CLAUDE.md bagian 15). */
class EvaluationRules
{
    public static function types(): array
    {
        return ['uts' => 'UTS', 'uas' => 'UAS'];
    }

    public static function questionType(string $evaluationType): string
    {
        return $evaluationType === 'uts' ? 'multiple_choice' : 'short_answer';
    }
}
