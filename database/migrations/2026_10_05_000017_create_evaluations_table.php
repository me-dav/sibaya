<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // uts | uas (App\Enums\EvaluationType)
            $table->string('type', 10)->index();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('passing_score')->default(75);
            $table->unsignedInteger('max_attempts')->default(1);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE evaluations ADD CONSTRAINT evaluations_passing_score_range CHECK (passing_score BETWEEN 0 AND 100)');
            DB::statement('ALTER TABLE evaluations ADD CONSTRAINT evaluations_duration_positive CHECK (duration_minutes > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
