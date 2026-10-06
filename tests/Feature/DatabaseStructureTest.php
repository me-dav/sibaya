<?php

namespace Tests\Feature;

use App\Enums\EvaluationType;
use App\Enums\ExerciseType;
use App\Enums\QuestionType;
use App\Models\Evaluation;
use App\Models\EvaluationAttempt;
use App\Models\Exercise;
use App\Models\Material;
use App\Models\Question;
use App\Models\RinenggaType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_stage_two_tables_exist(): void
    {
        foreach ([
            'users', 'rinengga_types', 'materials', 'exercises', 'questions', 'question_options',
            'exercise_attempts', 'exercise_answers', 'evaluations', 'evaluation_questions',
            'evaluation_attempts', 'evaluation_answers', 'recommendations', 'certificates',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} tidak ada");
        }

        // Tabel tahap akhir belum boleh ada
        $this->assertFalse(Schema::hasTable('puzzles'));
        $this->assertFalse(Schema::hasTable('puzzle_attempts'));
    }

    public function test_seeder_creates_six_rinengga_types_with_expected_slugs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            ['paribasan', 'bebasan', 'saloka', 'pepindhan', 'sanepa', 'tembung-entar'],
            RinenggaType::ordered()->pluck('slug')->all()
        );
        $this->assertSame(6, Material::count());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, RinenggaType::count());
    }

    public function test_material_belongs_to_type_and_published_scope_works(): void
    {
        $type = RinenggaType::factory()->create();
        Material::factory()->for($type, 'rinenggaType')->create();
        Material::factory()->for($type, 'rinenggaType')->draft()->create();

        $this->assertSame(2, $type->materials()->count());
        $this->assertSame(1, Material::published()->count());
    }

    public function test_exercise_questions_and_options_relations(): void
    {
        $exercise = Exercise::factory()->create(['exercise_type' => ExerciseType::CAMPURAN]);
        $question = Question::factory()->withOptions('C')->create(['exercise_id' => $exercise->id]);

        $this->assertSame(ExerciseType::CAMPURAN, $exercise->fresh()->exercise_type);
        $this->assertCount(1, $exercise->questions);
        $this->assertCount(4, $question->options);
        $this->assertSame('C', $question->options->firstWhere('is_correct', true)->option_key);
    }

    public function test_question_json_columns_are_cast_to_arrays(): void
    {
        $q = Question::factory()->create([
            'question_type' => QuestionType::TEXT_ANALYSIS,
            'metadata' => ['analysis_rows' => [['label' => 'Saloka', 'keywords' => ['x'], 'points' => 5]]],
            'keywords' => ['a', 'b'],
        ])->fresh();

        $this->assertSame('Saloka', $q->metadata['analysis_rows'][0]['label']);
        $this->assertSame(['a', 'b'], $q->keywords);
    }

    public function test_answer_key_is_hidden_from_serialization(): void
    {
        $q = Question::factory()->shortAnswer(['makna kias'])->create();

        $this->assertArrayNotHasKey('answer_key', $q->toArray());
        $this->assertArrayNotHasKey('keywords', $q->toArray());
    }

    public function test_evaluation_questions_pivot_is_unique(): void
    {
        $evaluation = Evaluation::factory()->create(['type' => EvaluationType::UTS]);
        $question = Question::factory()->create();

        $evaluation->questions()->attach($question->id, ['sort_order' => 1]);
        $this->assertCount(1, $evaluation->questions);

        $this->expectException(QueryException::class);
        $evaluation->questions()->attach($question->id, ['sort_order' => 2]);
    }

    public function test_user_has_attempt_relations(): void
    {
        $user = User::factory()->create();
        EvaluationAttempt::factory()->create(['user_id' => $user->id]);

        $this->assertCount(1, $user->evaluationAttempts);
    }

    public function test_rinengga_type_with_materials_cannot_be_deleted(): void
    {
        $material = Material::factory()->create();

        $this->expectException(QueryException::class);
        $material->rinenggaType->delete();
    }
}
