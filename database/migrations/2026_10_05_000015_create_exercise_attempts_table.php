<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->restrictOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('wrong_answers')->default(0);
            // in_progress | completed | abandoned (App\Enums\AttemptStatus)
            $table->string('status', 20)->default('in_progress');
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
    }
};
