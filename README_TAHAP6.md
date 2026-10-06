# siBAYA: Paket Tahap 6 (logika, tanpa view)

Form admin dinamis (UI) dan view siswa menunggu desain. Logika dan validasinya sudah lengkap
dan dites; UI admin di Tahap 7 tinggal memanggil QuestionService + StoreQuestionRequest.

## Baru
- app/Services/QuestionService.php
- app/Http/Requests/StoreQuestionRequest.php, UpdateQuestionRequest.php
- app/Http/Controllers/ExerciseAudioController.php
- tests/Feature/AnswerEvaluationTest.php, QuestionServiceTest.php, ExerciseTypesFlowTest.php

## Ditimpa (dari Tahap 5)
- app/Models/Question.php
- app/Services/AnswerEvaluationService.php, ExerciseAttemptService.php
- app/Http/Requests/SubmitExerciseRequest.php
- app/Http/Controllers/ExerciseAttemptController.php
- routes/web.php
- database/seeders/ExerciseSeeder.php
- tests/Feature/ExerciseAttemptTest.php  (1 test lama dihapus)

## Perintah
    php artisan migrate:fresh --seed
    php artisan test
