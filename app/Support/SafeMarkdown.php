<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Render Markdown aman untuk isi materi.
 * HTML mentah dibuang dan link berbahaya (javascript:, dll.) ditolak,
 * sehingga hasilnya aman dicetak tanpa {!! !!} langsung dari input user.
 */
class SafeMarkdown
{
    public static function render(?string $text): HtmlString
    {
        return new HtmlString(Str::markdown((string) $text, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
