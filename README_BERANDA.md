# siBAYA: Beranda + routing navbar

## Baru
- app/Http/Controllers/HomeController.php
- resources/views/home/index.blade.php
- resources/views/pages/coming-soon.blade.php   (halaman sementara 3 menu)
- tests/Feature/HomeAndNavigationTest.php

## Ditimpa (semua file buatan saya; cek `git diff` dulu bila sudah kamu ubah)
- routes/web.php
- resources/views/components/layouts/public.blade.php   (tambah font Caveat)
- resources/views/components/footer.blade.php
- resources/views/components/icon.blade.php             (tambah 6 ikon)

## TIDAK disentuh
- tailwind.config.js, resources/css/app.css

## Boleh dihapus
- resources/views/home/placeholder.blade.php (sudah tidak dipakai)

## Foto hero
Taruh di public/images/hero-beranda.jpg (otomatis dipakai).

## Saat fitur aslinya dibuat, hapus baris Route::view sementara di routes/web.php
- learning.index  -> Tahap 4
- evaluations.index -> Tahap 8
- puzzles.index   -> Tahap 11
