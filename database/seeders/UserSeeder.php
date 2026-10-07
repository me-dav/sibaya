<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('sibaya.admin.email');
        $password = config('sibaya.admin.password');
       

        $admin = User::firstOrNew(['email' => "admin@sibaya.com"]);
        $admin->name = "Admin SIBAYA";
        $admin->password = Hash::make("password");
        $admin->role = UserRole::ADMIN;
        $admin->is_active = true;
        $admin->email_verified_at ??= now();
        $admin->save();

        $this->command?->warn('Admin awal siap. Pastikan ADMIN_PASSWORD di .env kuat dan JANGAN di-commit.');
    }
}
