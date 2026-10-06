# siBAYA: Paket Tahap 5 (logika, tanpa view)

View (exercises.index, show, attempt, result) menunggu persetujuan desain.
Sebelum view dibuat, halaman GET index/show/attempt/result akan error "View not found".
POST start/submit sudah berfungsi dan dites.

## Baru
- app/Support/AnswerResult.php
- app/Services/AnswerEvaluationService.php   (pilihan ganda saja)
- app/Services/ExerciseAttemptService.php
- app/Policies/ExerciseAttemptPolicy.php
- app/Http/Requests/SubmitExerciseRequest.php
- app/Http/Controllers/ExerciseController.php
- app/Http/Controllers/ExerciseAttemptController.php
- database/seeders/ExerciseSeeder.php        (dummy)
- tests/Feature/ExerciseAttemptTest.php

## Ditimpa
- app/Models/Exercise.php        (route binding via slug)
- routes/web.php
- database/seeders/DatabaseSeeder.php

## Perintah
    php artisan migrate:fresh --seed
    php artisan route:list --name=exercises
    php artisan test --filter=ExerciseAttemptTest
