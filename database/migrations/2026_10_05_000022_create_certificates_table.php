<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('evaluation_attempt_id')->unique()->constrained('evaluation_attempts')->restrictOnDelete();
            $table->string('certificate_number')->unique();
            $table->decimal('score', 5, 2);
            $table->string('predicate')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
