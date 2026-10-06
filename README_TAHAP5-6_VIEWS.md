# siBAYA: View Gladhèn Mandiri (Tahap 5 dan 6)

## Baru
- resources/views/exercises/{index,show,attempt,result}.blade.php
- resources/views/components/icon.blade.php
- resources/views/components/audio-player.blade.php
- resources/views/components/exercise/{sidebar,shell}.blade.php
- tests/Feature/ExerciseViewsTest.php

## Ditimpa
- app/Enums/ExerciseType.php   (tambah description/icon/theme)
- tailwind.config.js           (tambah path ./app/Enums/**/*.php)
- tests/TestCase.php           (withoutVite agar test tidak butuh build)

## Foto hero (opsional)
Taruh foto di public/images/hero-gladhen.jpg. Jika ada, otomatis dipakai sebagai background hero.

## Perintah
    npm run dev          # atau npm run build
    php artisan test
