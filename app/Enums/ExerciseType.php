<?php

namespace App\Enums;

enum ExerciseType: string
{
    case PEMAHAMAN = 'pemahaman';
    case NYIMAK = 'nyimak';
    case ANALISIS = 'analisis';
    case MENURUT_MATERI = 'menurut_materi';
    case CAMPURAN = 'campuran';

    public function label(): string
    {
        return match ($this) {
            self::PEMAHAMAN => 'Latihan Pemahaman',
            self::NYIMAK => 'Latihan Nyimak',
            self::ANALISIS => 'Latihan Analisis',
            self::MENURUT_MATERI => 'Latihan Miturut Materi',
            self::CAMPURAN => 'Latihan Campuran',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PEMAHAMAN => 'Pilihan ganda lan isian cekak.',
            self::NYIMAK => 'Rungokna audio lan wangsulana pitakon.',
            self::ANALISIS => 'Temokake Basa Rinengga ing teks lan konteks.',
            self::MENURUT_MATERI => 'Fokus latihan saben jenis Basa Rinengga.',
            self::CAMPURAN => 'Gabungan kabeh materi kanggo nyoba pangertenmu.',
        };
    }

    /** Nama ikon untuk <x-icon>. */
    public function icon(): string
    {
        return match ($this) {
            self::PEMAHAMAN => 'document',
            self::NYIMAK => 'headphones',
            self::ANALISIS => 'search',
            self::MENURUT_MATERI => 'book',
            self::CAMPURAN => 'shuffle',
        };
    }

    /**
     * Kelas Tailwind per jenis (warna pastel seperti referensi).
     * Ditulis lengkap agar terdeteksi Tailwind (path app/Enums ada di tailwind.config.js).
     */
    public function theme(): array
    {
        return match ($this) {
            self::PEMAHAMAN => ['card' => 'bg-sky-50 border-sky-200 hover:border-sky-400', 'icon' => 'bg-sky-600 text-white', 'text' => 'text-sky-800', 'soft' => 'bg-sky-100 text-sky-900'],
            self::NYIMAK => ['card' => 'bg-emerald-50 border-emerald-200 hover:border-emerald-400', 'icon' => 'bg-emerald-600 text-white', 'text' => 'text-emerald-800', 'soft' => 'bg-emerald-100 text-emerald-900'],
            self::ANALISIS => ['card' => 'bg-violet-50 border-violet-200 hover:border-violet-400', 'icon' => 'bg-violet-600 text-white', 'text' => 'text-violet-800', 'soft' => 'bg-violet-100 text-violet-900'],
            self::MENURUT_MATERI => ['card' => 'bg-orange-50 border-orange-200 hover:border-orange-400', 'icon' => 'bg-orange-500 text-white', 'text' => 'text-orange-800', 'soft' => 'bg-orange-100 text-orange-900'],
            self::CAMPURAN => ['card' => 'bg-rose-50 border-rose-200 hover:border-rose-400', 'icon' => 'bg-rose-500 text-white', 'text' => 'text-rose-800', 'soft' => 'bg-rose-100 text-rose-900'],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
