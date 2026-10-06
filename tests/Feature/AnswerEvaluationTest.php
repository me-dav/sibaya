<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Services\AnswerEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private function svc(): AnswerEvaluationService
    {
        return app(AnswerEvaluationService::class);
    }

    private function short(array $keywords, string $key = 'peribahasa', array $meta = [], int $points = 3): Question
    {
        return Question::factory()->shortAnswer($keywords)->create([
            'answer_key' => $key, 'metadata' => $meta ?: null, 'points' => $points,
        ]);
    }

    private function analysis(): Question
    {
        return Question::factory()->create([
            'question_type' => QuestionType::TEXT_ANALYSIS,
            'passage_text' => 'teks',
            'points' => 5,
            'metadata' => ['analysis_rows' => [
                ['label' => 'Saloka', 'keywords' => ['kebo nusu gudel'], 'points' => 3],
                ['label' => 'Bebasan', 'keywords' => ['kacang ora ninggal lanjaran', 'kacang lanjaran'], 'points' => 2],
            ]],
        ]);
    }

    public function test_normalize_answer(): void
    {
        $this->assertSame('makna kias', $this->svc()->normalizeAnswer("  Makna   \n KIAS  "));
        $this->assertSame('', $this->svc()->normalizeAnswer(null));
    }

    public function test_keyword_must_match_whole_words(): void
    {
        $this->assertTrue($this->svc()->containsKeyword('peribahasa yang memiliki makna kias', 'Makna Kias'));
        $this->assertFalse($this->svc()->containsKeyword('ini kiasan saja', 'kias'));
        $this->assertFalse($this->svc()->containsKeyword('', 'kias'));
        $this->assertFalse($this->svc()->containsKeyword('apa saja', '   '));
    }

    public function test_short_answer_correct_by_keyword_case_and_spacing_insensitive(): void
    {
        $q = $this->short(['makna kias', 'arti kiasan']);

        $r = $this->svc()->evaluate($q, '  Peribahasa yang MEMILIKI   Makna  Kias ');
        $this->assertTrue($r->isCorrect);
        $this->assertSame(3, $r->pointsEarned);

        $this->assertTrue($this->svc()->evaluate($q, 'artinya Arti Kiasan')->isCorrect);
    }

    public function test_short_answer_matches_answer_key_too(): void
    {
        $q = $this->short(['xyz'], 'peribahasa');

        $this->assertTrue($this->svc()->evaluate($q, 'Peribahasa')->isCorrect);
    }

    public function test_short_answer_irrelevant_or_blank_is_wrong(): void
    {
        $q = $this->short(['makna kias']);

        $wrong = $this->svc()->evaluate($q, 'saya tidak tahu');
        $this->assertFalse($wrong->isCorrect);
        $this->assertSame(0, $wrong->pointsEarned);

        $blank = $this->svc()->evaluate($q, '   ');
        $this->assertFalse($blank->isCorrect);
        $this->assertNull($blank->answerText);
        $this->assertSame(0, $this->svc()->evaluate($q, null)->pointsEarned);
    }

    public function test_exact_mode_rejects_answer_that_merely_contains_keyword(): void
    {
        $q = $this->short(['saloka'], 'saloka', ['match' => 'exact']);

        $this->assertTrue($this->svc()->evaluate($q, ' SALOKA ')->isCorrect);
        $this->assertFalse($this->svc()->evaluate($q, 'bebasan, paribasan, saloka')->isCorrect);
    }

    public function test_text_analysis_gives_partial_points_per_row(): void
    {
        $q = $this->analysis();

        $r = $this->svc()->evaluate($q, [0 => 'Kebo Nusu Gudel', 1 => 'salah']);

        $this->assertSame(3, $r->pointsEarned);
        $this->assertFalse($r->isCorrect); // benar penuh hanya jika semua baris benar
        $this->assertSame(5, $q->maxPoints());
    }

    public function test_text_analysis_all_rows_correct_and_empty_rows_are_stored(): void
    {
        $q = $this->analysis();

        $full = $this->svc()->evaluate($q, [0 => 'kebo nusu gudel', 1 => 'kacang lanjaran']);
        $this->assertTrue($full->isCorrect);
        $this->assertSame(5, $full->pointsEarned);

        $empty = $this->svc()->evaluate($q, []);
        $this->assertSame(0, $empty->pointsEarned);
        $this->assertSame(['', ''], json_decode($empty->answerText, true)); // jawaban kosong tetap disimpan
    }

    public function test_analysis_breakdown_for_result_page(): void
    {
        $q = $this->analysis();
        $r = $this->svc()->evaluate($q, [0 => 'kebo nusu gudel', 1 => 'x']);

        $rows = $this->svc()->analysisBreakdown($q, $r->answerText);

        $this->assertTrue($rows[0]['is_correct']);
        $this->assertFalse($rows[1]['is_correct']);
        $this->assertSame('kacang ora ninggal lanjaran', $rows[1]['expected']);
    }

    public function test_analysis_labels_do_not_expose_keywords(): void
    {
        $q = $this->analysis();

        $this->assertSame(['Saloka', 'Bebasan'], $q->analysisLabels());
        $this->assertArrayNotHasKey('metadata', $q->toArray());
    }

    public function test_audio_choice_is_scored_like_multiple_choice(): void
    {
        $q = Question::factory()->withOptions('B')->create(['question_type' => QuestionType::AUDIO_CHOICE, 'points' => 2]);
        $q->load('options');
        $right = $q->options->firstWhere('option_key', 'B')->id;
        $wrong = $q->options->firstWhere('option_key', 'A')->id;

        $this->assertSame(2, $this->svc()->evaluate($q, $right)->pointsEarned);
        $this->assertFalse($this->svc()->evaluate($q, $wrong)->isCorrect);
    }
}
