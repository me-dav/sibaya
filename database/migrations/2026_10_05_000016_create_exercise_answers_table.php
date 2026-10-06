<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_attempt_id')->constrained('exercise_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->restrictOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_earned', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['exercise_attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_answers');
    }
};
