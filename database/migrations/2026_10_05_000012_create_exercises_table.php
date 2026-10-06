<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // pemahaman | nyimak | analisis | menurut_materi | campuran (App\Enums\ExerciseType)
            $table->string('exercise_type', 30)->index();
            $table->foreignId('rinengga_type_id')->nullable()->constrained('rinengga_types')->restrictOnDelete();
            $table->text('instructions')->nullable();
            $table->boolean('is_randomized')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
