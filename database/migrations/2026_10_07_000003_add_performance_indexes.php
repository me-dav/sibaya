<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks tambahan untuk query yang sering dipakai. Tidak mengubah kolom/struktur tabel
 * (CLAUDE.md bagian 19). PostgreSQL tidak membuat indeks otomatis untuk kolom foreign key.
 * Aman dijalankan ulang: indeks yang sudah ada dilewati.
 */
return new class extends Migration
{
    private const INDEXES = [
        'users'               => [['role', 'is_active'], ['class_name']],
        'materials'           => [['rinengga_type_id', 'is_published']],
        'exercises'           => [['exercise_type', 'is_published'], ['rinengga_type_id']],
        'questions'           => [['exercise_id', 'sort_order'], ['question_type']],
        'question_options'    => [['question_id']],
        'exercise_attempts'   => [['user_id', 'exercise_id'], ['exercise_id']],
        'exercise_answers'    => [['exercise_attempt_id'], ['question_id'], ['selected_option_id']],
        'evaluation_attempts' => [['user_id', 'evaluation_id', 'status'], ['evaluation_id']],
        'evaluation_answers'  => [['evaluation_attempt_id', 'question_id'], ['question_id'], ['selected_option_id']],
        'recommendations'     => [['user_id'], ['source_evaluation_attempt_id']],
        'certificates'        => [['user_id']],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $sets) {
            foreach ($sets as $columns) {
                if (Schema::hasTable($table) && Schema::hasColumns($table, $columns) && ! Schema::hasIndex($table, $columns)) {
                    Schema::table($table, fn (Blueprint $t) => $t->index($columns));
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $sets) {
            foreach ($sets as $columns) {
                if (Schema::hasTable($table) && Schema::hasIndex($table, $columns)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($columns));
                }
            }
        }
    }
};
