<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Exercise;
use App\Models\ExerciseAnswer;
use App\Models\ExerciseAttempt;
use App\Models\Question;
use App\Models\User;
use App\Services\QuestionService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class QuestionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function svc(): QuestionService
    {
        return app(QuestionService::class);
    }

    private function mcData(array $o = []): array
    {
        return array_merge([
            'question_type' => 'multiple_choice',
            'question_text' => 'Apa iku saloka?',
            'option_a' => 'A satu', 'option_b' => 'B dua', 'option_c' => 'C tiga', 'option_d' => '',
            'correct_option' => 'B',
            'points' => 2,
        ], $o);
    }

    private function validate(array $data, string $class = StoreQuestionRequest::class, $route = null): \Illuminate\Validation\Validator
    {
        $request = $class::create('/x', 'POST', $data);
        if ($route) {
            $request->setRouteResolver(fn () => new class($route) {
                public function __construct(private $q) {}
                public function parameter($k, $d = null) { return $this->q; }
            });
        }

        return Validator::make($data, $request->rules());
    }

    // ---- pembuatan per tipe ----

    public function test_create_multiple_choice_with_options_and_key(): void
    {
        $exercise = Exercise::factory()->create();
        $q = $this->svc()->create($this->mcData(['exercise_id' => $exercise->id]));

        $this->assertSame(QuestionType::MULTIPLE_CHOICE, $q->question_type);
        $this->assertSame(3, $q->options()->count()); // opsi D kosong tidak dibuat
        $this->assertSame('B', $q->options()->where('is_correct', true)->value('option_key'));
        $this->assertSame('B', $q->answer_key);
        $this->assertSame(1, $q->sort_order);
    }

    public function test_create_audio_choice_stores_file_on_private_disk(): void
    {
        Storage::fake('local');
        $q = $this->svc()->create(
            $this->mcData(['question_type' => 'audio_choice']),
            UploadedFile::fake()->create('rekaman.mp3', 200, 'audio/mpeg')
        );

        $this->assertStringStartsWith('audio/questions/', $q->audio_path);
        $this->assertStringEndsWith('.mp3', $q->audio_path);
        Storage::disk('local')->assertExists($q->audio_path);
        Storage::disk('public')->assertMissing($q->audio_path); // tidak publik
    }

    public function test_audio_choice_without_file_is_rejected(): void
    {
        $this->expectException(DomainException::class);
        $this->svc()->create($this->mcData(['question_type' => 'audio_choice']));
    }

    public function test_create_text_analysis_sums_row_points_and_normalizes_keywords(): void
    {
        $q = $this->svc()->create([
            'question_type' => 'text_analysis',
            'passage_text' => 'teks',
            'analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => "a, b\n a ;c", 'points' => 3],
                ['label' => 'Bebasan', 'keywords' => ['x'], 'points' => 2],
            ],
        ]);

        $this->assertSame(5, $q->points);
        $this->assertSame(5, $q->maxPoints());
        $this->assertSame(['a', 'b', 'c'], $q->metadata['analysis_rows'][0]['keywords']);
    }

    public function test_create_short_answer(): void
    {
        $q = $this->svc()->create([
            'question_type' => 'short_answer',
            'question_text' => 'Jelaskan',
            'answer_key' => ' makna kias ',
            'keywords' => "arti kiasan\nmakna kias",
            'points' => 4,
        ]);

        $this->assertSame('makna kias', $q->answer_key);
        $this->assertSame(['arti kiasan', 'makna kias'], $q->keywords);
        $this->assertSame('contains', $q->metadata['match']);
    }

    // ---- update dan hapus ----

    public function test_replacing_audio_deletes_old_file_when_unused(): void
    {
        Storage::fake('local');
        $q = $this->svc()->create($this->mcData(['question_type' => 'audio_choice']), UploadedFile::fake()->create('a.mp3', 50, 'audio/mpeg'));
        $old = $q->audio_path;

        $q = $this->svc()->update($q, $this->mcData(), UploadedFile::fake()->create('b.ogg', 50, 'audio/ogg'));

        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($q->audio_path);
        $this->assertNotSame($old, $q->audio_path);
    }

    public function test_old_audio_is_kept_if_another_question_still_uses_it(): void
    {
        Storage::fake('local');
        $q = $this->svc()->create($this->mcData(['question_type' => 'audio_choice']), UploadedFile::fake()->create('a.mp3', 50, 'audio/mpeg'));
        $old = $q->audio_path;
        Question::factory()->create(['audio_path' => $old]);

        $this->svc()->update($q, $this->mcData(), UploadedFile::fake()->create('b.mp3', 50, 'audio/mpeg'));

        Storage::disk('local')->assertExists($old);
    }

    public function test_option_already_chosen_by_student_cannot_be_removed(): void
    {
        $q = $this->svc()->create($this->mcData(['exercise_id' => Exercise::factory()->create()->id]));
        $attempt = ExerciseAttempt::factory()->create(['exercise_id' => $q->exercise_id]);
        ExerciseAnswer::create([
            'exercise_attempt_id' => $attempt->id,
            'question_id' => $q->id,
            'selected_option_id' => $q->options()->where('option_key', 'C')->value('id'),
            'is_correct' => false,
        ]);

        $this->expectException(DomainException::class);
        $this->svc()->update($q, $this->mcData(['option_c' => '', 'correct_option' => 'A']));
    }

    public function test_answered_question_cannot_be_deleted_but_unused_can(): void
    {
        Storage::fake('local');
        $used = $this->svc()->create($this->mcData(['exercise_id' => Exercise::factory()->create()->id]));
        $attempt = ExerciseAttempt::factory()->create(['exercise_id' => $used->exercise_id]);
        ExerciseAnswer::create(['exercise_attempt_id' => $attempt->id, 'question_id' => $used->id, 'is_correct' => false]);

        $free = $this->svc()->create($this->mcData(['question_type' => 'audio_choice']), UploadedFile::fake()->create('a.mp3', 50, 'audio/mpeg'));
        $path = $free->audio_path;

        $this->svc()->delete($free);
        $this->assertModelMissing($free);
        Storage::disk('local')->assertMissing($path);

        $this->expectException(DomainException::class);
        $this->svc()->delete($used);
    }

    // ---- validasi bersyarat ----

    public function test_validation_multiple_choice(): void
    {
        $this->assertTrue($this->validate($this->mcData())->passes());

        $v = $this->validate($this->mcData(['correct_option' => 'D'])); // D kosong
        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('correct_option', $v->errors()->toArray());

        $this->assertArrayHasKey('points', $this->validate($this->mcData(['points' => 0]))->errors()->toArray());
        $this->assertArrayHasKey('points', $this->validate($this->mcData(['points' => -1]))->errors()->toArray());
        $this->assertArrayHasKey('option_b', $this->validate($this->mcData(['option_b' => '']))->errors()->toArray());
    }

    public function test_validation_audio_requires_valid_audio_file(): void
    {
        $data = $this->mcData(['question_type' => 'audio_choice']);

        $this->assertArrayHasKey('audio_file', $this->validate($data)->errors()->toArray());

        $this->assertArrayHasKey('audio_file', $this->validate($data + [
            'audio_file' => UploadedFile::fake()->create('virus.php', 10, 'text/plain'),
        ])->errors()->toArray());

        $this->assertTrue($this->validate($data + [
            'audio_file' => UploadedFile::fake()->create('ok.mp3', 100, 'audio/mpeg'),
        ])->passes());

        $this->assertArrayHasKey('audio_file', $this->validate($data + [
            'audio_file' => UploadedFile::fake()->create('big.mp3', 20000, 'audio/mpeg'),
        ])->errors()->toArray());
    }

    public function test_validation_text_analysis_and_short_answer(): void
    {
        $ok = ['question_type' => 'text_analysis', 'passage_text' => 't',
            'analysis_rows' => [['label' => 'Saloka', 'keywords' => 'a', 'points' => 1]]];
        $this->assertTrue($this->validate($ok)->passes());

        $bad = $ok;
        $bad['analysis_rows'][0]['keywords'] = ' , ;';
        $this->assertTrue($this->validate($bad)->fails());

        $this->assertArrayHasKey('analysis_rows', $this->validate(['question_type' => 'text_analysis', 'passage_text' => 't'])->errors()->toArray());

        $sa = ['question_type' => 'short_answer', 'question_text' => 'q', 'answer_key' => 'k', 'points' => 1];
        $this->assertTrue($this->validate($sa)->passes());
        $this->assertArrayHasKey('answer_key', $this->validate(['answer_key' => ''] + $sa)->errors()->toArray());
        $this->assertArrayHasKey('match_mode', $this->validate($sa + ['match_mode' => 'fuzzy'])->errors()->toArray());
    }

    public function test_only_admin_is_authorized_to_manage_questions(): void
    {
        $req = fn (User $u) => tap(StoreQuestionRequest::create('/x', 'POST'), fn ($r) => $r->setUserResolver(fn () => $u));

        $this->assertTrue($req(User::factory()->admin()->create())->authorize());
        $this->assertFalse($req(User::factory()->create())->authorize());
    }
}
