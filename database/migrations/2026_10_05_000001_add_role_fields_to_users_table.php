<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nilai valid: admin | siswa (divalidasi lewat enum App\Enums\UserRole)
            $table->string('role', 20)->default('siswa')->after('password')->index();
            $table->string('class_name')->nullable()->after('role');
            $table->string('school_name')->nullable()->after('class_name');
            $table->boolean('is_active')->default(true)->after('school_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'class_name', 'school_name', 'is_active']);
        });
    }
};
