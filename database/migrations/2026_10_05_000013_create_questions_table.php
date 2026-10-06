<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // nullable: soal juga bisa dipakai di evaluasi tanpa latihan
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->restrictOnDelete();
            // multiple_choice | audio_choice | text_analysis | short_answer (App\Enums\QuestionType)
            $table->string('question_type', 30)->index();
            $table->text('question_text')->nullable();
            $table->longText('passage_text')->nullable();
            $table->string('audio_path')->nullable();
            $table->text('answer_key')->nullable();
            $table->jsonb('keywords')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions ADD CONSTRAINT questions_points_non_negative CHECK (points >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
