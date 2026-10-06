<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('option_key', 1); // A, B, C, D
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['question_id', 'option_key']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE question_options ADD CONSTRAINT question_options_key_check CHECK (option_key IN ('A','B','C','D'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
