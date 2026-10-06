<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('evaluation_id')->constrained('evaluations')->restrictOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('wrong_answers')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            // in_progress | completed | expired | abandoned (App\Enums\EvaluationAttemptStatus)
            $table->string('status', 20)->default('in_progress');
            $table->timestamps();

            $table->index(['user_id', 'evaluation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_attempts');
    }
};
