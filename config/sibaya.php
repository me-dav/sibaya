<?php

/**
 * Konfigurasi tampilan publik SiBAYA.
 * Label dipusatkan di sini supaya istilah tidak berubah-ubah antar halaman.
 */
return [
    'name'    => 'SiBAYA',
    'tagline' => 'Sinau Basa lan Budaya Jawa',

    // Lima menu publik utama (CLAUDE.md bagian 6).
    'nav' => [
        ['label' => 'Beranda',          'route' => 'home',              'match' => 'home'],
        ['label' => 'Ruang Piwulang',   'route' => 'learning.index',    'match' => 'learning.*'],
        ['label' => 'Gladhen Mandiri',  'route' => 'exercises.index',   'match' => 'exercises.*'],
        ['label' => 'Evaluasi',         'route' => 'evaluations.index', 'match' => 'evaluations.*'],
        ['label' => 'Wahana Teka-teki', 'route' => 'puzzles.index',     'match' => 'puzzles.*'],
    ],

    'footer_quote' => [
        'Basa Jawa ora mung tembung,',
        'nanging jati diri lan warisan budaya.',
    ],
];
