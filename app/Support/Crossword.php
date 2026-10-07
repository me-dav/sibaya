<?php

namespace App\Support;

/**
 * Tata letak teka-teki silang dari daftar kata (baris/kolom 1-based pada konfigurasi).
 * Koordinat hasil bersifat 0-based. Kunci jawaban (huruf) TIDAK pernah ikut ke browser:
 * gunakan publicLayout() untuk data yang dikirim ke siswa.
 */
class Crossword
{
    /**
     * @param array{rows: int, cols: int, words: array<int, array>} $config
     * @return array{rows: int, cols: int, grid: array, numbers: array<string,int>, words: array, errors: array<int,string>}
     */
    public static function layout(array $config): array
    {
        $rows = (int) ($config['rows'] ?? 0);
        $cols = (int) ($config['cols'] ?? 0);
        $grid = $rows > 0 && $cols > 0 ? array_fill(0, $rows, array_fill(0, $cols, null)) : [];
        $errors = [];
        $words = [];

        foreach (array_values($config['words'] ?? []) as $i => $w) {
            $letters = mb_str_split((string) $w['answer']);
            $down = ($w['direction'] ?? 'across') === 'down';
            $r = (int) $w['row'] - 1;
            $c = (int) $w['col'] - 1;
            $n = count($letters);
            $endR = $r + ($down ? $n - 1 : 0);
            $endC = $c + ($down ? 0 : $n - 1);
            $label = 'Tembung '.($i + 1).' ('.$w['answer'].')';

            if ($r < 0 || $c < 0 || $endR >= $rows || $endC >= $cols) {
                $errors[] = "{$label} metu saka kothak (ukuran {$rows}x{$cols}).";
                continue;
            }

            $cells = [];
            foreach ($letters as $k => $letter) {
                $rr = $r + ($down ? $k : 0);
                $cc = $c + ($down ? 0 : $k);

                if ($grid[$rr][$cc] !== null && $grid[$rr][$cc] !== $letter) {
                    $errors[] = "{$label} mbentur huruf liya ing baris ".($rr + 1).', kolom '.($cc + 1).'.';
                } else {
                    $grid[$rr][$cc] = $letter;
                }
                $cells[] = [$rr, $cc];
            }

            $words[] = $w + ['cells' => $cells, 'index' => $i];
        }

        // Dua kata searah tidak boleh tumpang tindih atau bersambung langsung (jadi satu kata panjang).
        foreach ($words as $a => $wa) {
            foreach ($words as $b => $wb) {
                if ($b <= $a || $wa['direction'] !== $wb['direction']) {
                    continue;
                }

                $keysA = array_map(fn ($x) => $x[0].'-'.$x[1], $wa['cells']);
                $keysB = array_map(fn ($x) => $x[0].'-'.$x[1], $wb['cells']);

                if (array_intersect($keysA, $keysB) || array_intersect(self::neighbours($wa), $keysB) || array_intersect(self::neighbours($wb), $keysA)) {
                    $errors[] = 'Tembung '.($wa['index'] + 1).' lan '.($wb['index'] + 1).' ora oleh tumpang tindih utawa nyambung langsung.';
                }
            }
        }

        // Penomoran: sel awal kata, berurutan baris lalu kolom; awal across+down di sel sama berbagi nomor.
        $starts = [];
        foreach ($words as $w) {
            $starts[$w['cells'][0][0].'-'.$w['cells'][0][1]] = [$w['cells'][0][0], $w['cells'][0][1]];
        }
        uasort($starts, fn ($x, $y) => [$x[0], $x[1]] <=> [$y[0], $y[1]]);

        $numbers = [];
        $n = 0;
        foreach (array_keys($starts) as $key) {
            $numbers[$key] = ++$n;
        }

        foreach ($words as $k => $w) {
            $words[$k]['number'] = $numbers[$w['cells'][0][0].'-'.$w['cells'][0][1]];
        }

        return compact('rows', 'cols', 'grid', 'numbers', 'words', 'errors');
    }

    /** Sel tepat sebelum awal dan sesudah akhir sebuah kata. */
    private static function neighbours(array $w): array
    {
        $down = $w['direction'] === 'down';
        [$fr, $fc] = $w['cells'][0];
        [$lr, $lc] = $w['cells'][count($w['cells']) - 1];

        return [
            ($fr - ($down ? 1 : 0)).'-'.($fc - ($down ? 0 : 1)),
            ($lr + ($down ? 1 : 0)).'-'.($lc + ($down ? 0 : 1)),
        ];
    }

    /** Data aman untuk siswa: kotak aktif, nomor, dan petunjuk (tanpa huruf jawaban). */
    public static function publicLayout(array $layout): array
    {
        $active = [];
        foreach ($layout['words'] as $w) {
            foreach ($w['cells'] as [$r, $c]) {
                $active["{$r}-{$c}"] = true;
            }
        }

        $clues = ['across' => [], 'down' => []];
        foreach ($layout['words'] as $w) {
            $clues[$w['direction']][] = [
                'number' => $w['number'], 'clue' => $w['clue'], 'length' => count($w['cells']), 'points' => (int) $w['points'],
            ];
        }
        foreach ($clues as $dir => $list) {
            usort($list, fn ($a, $b) => $a['number'] <=> $b['number']);
            $clues[$dir] = $list;
        }

        return ['rows' => $layout['rows'], 'cols' => $layout['cols'], 'active' => $active, 'numbers' => $layout['numbers'], 'clues' => $clues];
    }
}
