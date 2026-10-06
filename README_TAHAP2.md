# siBAYA: Paket Tahap 2

Salin ke root project (timpa file yang sama). Jalankan SETELAH paket Tahap 1.

## Baru
- app/Enums: ExerciseType, QuestionType, EvaluationType, AttemptStatus, EvaluationAttemptStatus
- app/Models: RinenggaType, Material, Exercise, Question, QuestionOption, ExerciseAttempt,
  ExerciseAnswer, Evaluation, EvaluationQuestion, EvaluationAttempt, EvaluationAnswer,
  Recommendation, Certificate
- database/migrations/2026_10_05_000010 ... 000022 (13 tabel)
- database/factories: 10 factory
- database/seeders: RinenggaTypeSeeder, MaterialSeeder (dummy)
- tests/Feature/DatabaseStructureTest.php

## Ditimpa
- app/Models/User.php (tambah relasi)
- database/seeders/DatabaseSeeder.php

## Perintah
    php artisan migrate:fresh --seed     # database dev, aman dihapus ulang
    php artisan test
    php artisan tinker --execute="dump(App\Models\RinenggaType::pluck('name'))"
