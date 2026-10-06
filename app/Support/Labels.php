<?php

namespace App\Support;

use BackedEnum;

/** Label tampilan dan aturan tipe soal per jenis latihan, dipusatkan di satu tempat. */
class Labels
{
    public static function exerciseTypes(): array
    {
        return [
            'pemahaman'      => 'Pemahaman',
            'nyimak'         => 'Nyimak',
            'analisis'       => 'Analisis',
            'menurut_materi' => 'Menurut Materi',
            'campuran'       => 'Campuran',
        ];
    }

    public static function questionTypes(): array
    {
        return [
            'multiple_choice' => 'Pilihan Ganda',
            'audio_choice'    => 'Pilihan Ganda Audio',
            'text_analysis'   => 'Analisis Teks',
            'short_answer'    => 'Isian Singkat',
        ];
    }

    /** Tipe soal yang boleh dipakai pada tiap jenis latihan (CLAUDE.md bagian 12). */
    public static function allowedQuestionTypes(?string $exerciseType): array
    {
        return match ($exerciseType) {
            'pemahaman' => ['multiple_choice', 'short_answer'],
            'nyimak'    => ['audio_choice'],
            'analisis'  => ['text_analysis'],
            default     => array_keys(self::questionTypes()), // menurut_materi, campuran
        };
    }

    /** Nilai string dari enum atau string biasa. */
    public static function value(mixed $v): ?string
    {
        return $v instanceof BackedEnum ? (string) $v->value : ($v === null ? null : (string) $v);
    }

    /** "a, b\nc" -> ['a','b','c'] */
    public static function splitKeywords(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', (string) $text))));
    }
}
