<?php

namespace App\Support;

/**
 * Mengubah URL video menjadi URL embed untuk penyedia yang diizinkan
 * (YouTube dan Vimeo). URL lain tidak di-embed, hanya ditampilkan sebagai link.
 */
class VideoEmbed
{
    public static function embedUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $parts = parse_url($url);
        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return null;
        }

        $host = preg_replace('/^www\./', '', strtolower($parts['host']));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $id = null;
        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                $id = $query['v'] ?? null;
            } elseif (preg_match('#^/(?:embed|shorts|live)/([\w-]{11})#', $path, $m)) {
                $id = $m[1];
            }
        } elseif ($host === 'youtu.be') {
            $id = explode('/', ltrim($path, '/'))[0] ?: null;
        }

        if (is_string($id) && preg_match('/^[\w-]{11}$/', $id)) {
            return 'https://www.youtube-nocookie.com/embed/'.$id;
        }

        if ($host === 'vimeo.com' && preg_match('#^/(\d+)#', $path, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
