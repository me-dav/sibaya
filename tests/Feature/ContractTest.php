<?php

namespace Tests\Feature;

use App\Services\AnswerEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\Support\MakesMaterials;
use Tests\TestCase;

/**
 * JALANKAN INI PERTAMA. Test ini tidak menguji fitur, melainkan memeriksa asumsi kode Tahap 3-11
 * terhadap bagian project yang dikerjakan di luar (Tahap 1, 2, 5, 6). Jika ada yang gagal,
 * perbaiki dulu di sini supaya tidak muncul puluhan kegagalan turunan di test fitur.
 */
class ContractTest extends TestCase
{
    use MakesMaterials, RefreshDatabase;

    public function test_tabel_dan_kolom_sesuai_siBAYA_md(): void
    {
        $spec = [
            'users'               => ['role', 'class_name', 'school_name', 'is_active'],
            'rinengga_types'      => ['name', 'slug', 'short_description', 'description', 'sort_order', 'is_published'],
            'materials'           => ['rinengga_type_id', 'title', 'slug', 'description', 'content', 'examples', 'summary', 'image_path', 'audio_path', 'video_url', 'is_published'],
            'exercises'           => ['title', 'slug', 'description', 'exercise_type', 'rinengga_type_id', 'instructions', 'is_randomized', 'is_published'],
            'questions'           => ['exercise_id', 'question_type', 'question_text', 'passage_text', 'audio_path', 'answer_key', 'keywords', 'points', 'sort_order', 'metadata'],
            'question_options'    => ['question_id', 'option_key', 'option_text', 'is_correct', 'sort_order'],
            'exercise_attempts'   => ['user_id', 'exercise_id', 'started_at', 'completed_at', 'score', 'total_points', 'correct_answers', 'wrong_answers', 'status'],
            'exercise_answers'    => ['exercise_attempt_id', 'question_id', 'selected_option_id', 'answer_text', 'is_correct', 'points_earned'],
            'evaluations'         => ['title', 'type', 'description', 'instructions', 'duration_minutes', 'passing_score', 'max_attempts', 'start_at', 'end_at', 'is_published'],
            'evaluation_questions' => ['evaluation_id', 'question_id', 'sort_order'],
            'evaluation_attempts' => ['user_id', 'evaluation_id', 'started_at', 'submitted_at', 'score', 'total_points', 'correct_answers', 'wrong_answers', 'duration_seconds', 'status'],
            'evaluation_answers'  => ['evaluation_attempt_id', 'question_id', 'selected_option_id', 'answer_text', 'is_correct', 'points_earned'],
            'recommendations'     => ['user_id', 'material_id', 'source_evaluation_attempt_id', 'reason', 'is_read'],
            'certificates'        => ['user_id', 'evaluation_attempt_id', 'certificate_number', 'score', 'predicate', 'pdf_path', 'issued_at'],
            'puzzles'             => ['title', 'slug', 'description', 'puzzle_type', 'difficulty', 'instructions', 'configuration', 'is_published'],
            'puzzle_attempts'     => ['user_id', 'puzzle_id', 'score', 'duration_seconds', 'completed_at'],
        ];

        $missing = [];
        foreach ($spec as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = "tabel {$table}";
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }

        $this->assertSame([], $missing, 'Hilang: '.implode(', ', $missing));
    }

    public function test_relasi_eloquent_yang_diasumsikan_ada(): void
    {
        $relations = [
            \App\Models\Material::class          => ['rinenggaType', 'recommendations'],
            \App\Models\RinenggaType::class      => ['materials'],
            \App\Models\Exercise::class          => ['questions'],
            \App\Models\Question::class          => ['options'],
            \App\Models\ExerciseAttempt::class   => ['user', 'exercise'],
            \App\Models\ExerciseAnswer::class    => ['question', 'selectedOption'],
            \App\Models\EvaluationAttempt::class => ['user', 'evaluation'],
            \App\Models\User::class              => ['exerciseAttempts', 'evaluationAttempts'],
        ];

        $missing = [];
        foreach ($relations as $model => $methods) {
            foreach ($methods as $method) {
                if (! method_exists($model, $method)) {
                    $missing[] = class_basename($model)."::{$method}()";
                }
            }
        }

        $this->assertSame([], $missing, 'Relasi hilang: '.implode(', ', $missing));
    }

    public function test_enum_dan_role(): void
    {
        foreach (['UserRole', 'ExerciseType', 'QuestionType', 'EvaluationType', 'PuzzleType'] as $enum) {
            $this->assertTrue(enum_exists("App\\Enums\\{$enum}"), "Enum {$enum} tidak ada");
        }

        $this->assertSame('admin', \App\Enums\UserRole::ADMIN->value);
        $this->assertSame('siswa', \App\Enums\UserRole::SISWA->value);
    }

    public function test_answer_evaluation_service_menyediakan_method_yang_dipakai_evaluasi(): void
    {
        foreach (['evaluateMultipleChoice', 'evaluateShortAnswer'] as $method) {
            $this->assertTrue(method_exists(AnswerEvaluationService::class, $method), "AnswerEvaluationService::{$method}() tidak ada");
            $this->assertGreaterThanOrEqual(2, (new ReflectionMethod(AnswerEvaluationService::class, $method))->getNumberOfParameters(),
                "{$method}() harus menerima (Question, jawaban). Jika berbeda, sesuaikan EvaluationService::grade().");
        }
    }

    public function test_komponen_layout_dan_middleware_dari_tahap_1(): void
    {
        foreach (['components.layouts.admin', 'components.layouts.public', 'components.input-error'] as $view) {
            $this->assertTrue(view()->exists($view), "View {$view} tidak ada");
        }

        $this->assertArrayHasKey('role', app('router')->getMiddleware(), "Alias middleware 'role' belum terdaftar");

        foreach (['home', 'login', 'logout', 'admin.dashboard'] as $name) {
            $this->assertTrue(Route::has($name), "Route {$name} belum ada");
        }
    }

    public function test_semua_route_yang_dipakai_kode_terdaftar(): void
    {
        $names = [
            'learning.index', 'learning.show', 'evaluations.index', 'evaluations.show', 'evaluations.start', 'evaluations.attempts.show',
            'evaluations.attempts.answers', 'evaluations.submit', 'evaluations.attempts.result', 'puzzles.index', 'puzzles.show', 'puzzles.start',
            'puzzles.play', 'puzzles.submit', 'puzzles.result', 'dashboard', 'recommendations.open', 'certificates.verify', 'certificates.issue',
            'certificates.show', 'certificates.download', 'admin.materials.index', 'admin.students.index', 'admin.exercises.index',
            'admin.exercises.show', 'admin.exercises.questions.create', 'admin.questions.edit', 'admin.questions.move', 'admin.questions.audio',
            'admin.results.index', 'admin.results.exercises.show', 'admin.results.evaluations.show', 'admin.evaluations.index',
            'admin.evaluations.show', 'admin.evaluations.questions.create', 'admin.evaluations.results', 'admin.puzzles.index', 'admin.puzzles.attempts',
        ];

        $missing = array_values(array_filter($names, fn ($n) => ! Route::has($n)));

        $this->assertSame([], $missing, 'Route belum terdaftar: '.implode(', ', $missing));
    }

    public function test_dependency_pdf_terpasang_dan_disk_tersedia(): void
    {
        $this->assertTrue(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class), 'Jalankan: composer require barryvdh/laravel-dompdf');
        $this->assertArrayHasKey('public', config('filesystems.disks'));
        $this->assertArrayHasKey(config('sibaya.private_disk', 'local'), config('filesystems.disks'));
        $this->assertIsArray(config('sibaya.nav'), 'config/sibaya.php dari Tahap 3 belum ada');
    }

    public function test_factory_user_dan_role(): void
    {
        $value = fn ($user) => $user->role instanceof \BackedEnum ? $user->role->value : $user->role;

        $this->assertSame('admin', $value($this->admin()));
        $this->assertSame('siswa', $value($this->siswa()));
    }
}
