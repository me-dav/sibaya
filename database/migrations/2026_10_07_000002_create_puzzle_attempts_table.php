<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puzzle_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('puzzle_id')->constrained()->restrictOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'puzzle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puzzle_attempts');
    }
};
