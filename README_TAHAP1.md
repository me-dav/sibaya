# siBAYA: Paket Tahap 1

Cara pakai: salin isi folder ini ke root project Laravel kamu (timpa file yang sama).
Jalankan SETELAH `php artisan breeze:install blade` selesai.

## File baru
- app/Enums/UserRole.php
- app/Http/Middleware/EnsureUserHasRole.php
- app/Http/Middleware/EnsureUserIsActive.php
- config/sibaya.php
- database/migrations/2026_10_05_000001_add_role_fields_to_users_table.php
- database/seeders/UserSeeder.php
- resources/views/components/layouts/public.blade.php
- resources/views/components/navbar.blade.php
- resources/views/components/footer.blade.php
- resources/views/home/placeholder.blade.php   (sementara)
- resources/views/admin/placeholder.blade.php  (sementara)
- tests/Feature/RoleAuthorizationTest.php
- tests/Feature/StudentRegistrationTest.php

## File yang ditimpa
- app/Models/User.php
- app/Http/Controllers/Auth/RegisteredUserController.php
- bootstrap/app.php
- routes/web.php
- database/factories/UserFactory.php
- database/seeders/DatabaseSeeder.php
- tailwind.config.js
- resources/views/layouts/guest.blade.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- resources/views/components/primary-button.blade.php
- resources/views/components/text-input.blade.php

## Edit manual (1 baris)
Tambahkan di bawah tiga baris `@tailwind` pada resources/css/app.css:

    [x-cloak] { display: none !important; }

## Perintah
    php artisan migrate
    php artisan db:seed
    php artisan test
    npm run dev
    php artisan serve
