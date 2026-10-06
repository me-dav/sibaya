<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Support\AnswerResult;

/**
 * Semua penilaian dilakukan di server. Frontend tidak pernah menjadi sumber nilai.
 *
 * Format jawaban mentah per tipe:
 *  - multiple_choice / audio_choice : id opsi (int)
 *  - short_answer                   : string
 *  - text_analysis                  : array indeks_baris => string
 */
class AnswerEvaluationService
{
    public function evaluate(Question $question, mixed $raw): AnswerResult
    {
        return match ($question->question_type) {
            QuestionType::MULTIPLE_CHOICE => $this->evaluateMultipleChoice($question, $this->toIntOrNull($raw)),
            QuestionType::AUDIO_CHOICE => $this->evaluateAudioChoice($question, $this->toIntOrNull($raw)),
            QuestionType::SHORT_ANSWER => $this->evaluateShortAnswer($question, is_string($raw) ? $raw : null),
            QuestionType::TEXT_ANALYSIS => $this->evaluateTextAnalysis($question, is_array($raw) ? $raw : []),
        };
    }

    /**
     * Opsi harus milik soal ini. Opsi dari soal lain dianggap jawaban kosong.
     */
    public function evaluateMultipleChoice(Question $question, ?int $selectedOptionId): AnswerResult
    {
        $selected = $selectedOptionId === null
            ? null
            : $question->options->firstWhere('id', $selectedOptionId);

        if ($selected === null) {
            return new AnswerResult(null, null, false, 0);
        }

        $correct = (bool) $selected->is_correct;

        return new AnswerResult($selected->id, null, $correct, $correct ? (int) $question->points : 0);
    }

    public function evaluateAudioChoice(Question $question, ?int $selectedOptionId): AnswerResult
    {
        return $this->evaluateMultipleChoice($question, $selectedOptionId);
    }

    /**
     * Benar jika jawaban cocok dengan answer_key atau salah satu kata kunci.
     * metadata.match = 'contains' (default) atau 'exact'.
     */
    public function evaluateShortAnswer(Question $question, ?string $answer): AnswerResult
    {
        $normalized = $this->normalizeAnswer($answer);

        if ($normalized === '') {
            return new AnswerResult(null, null, false, 0);
        }

        $correct = $this->matchesQuestion($question, $normalized);

        return new AnswerResult(null, trim((string) $answer), $correct, $correct ? (int) $question->points : 0);
    }

    /**
     * Tiap baris dinilai sendiri. Soal dianggap benar (is_correct) hanya jika
     * SEMUA baris benar; poin tetap diberikan per baris (nilai parsial).
     * Jawaban siswa disimpan sebagai JSON di answer_text.
     */
    public function evaluateTextAnalysis(Question $question, array $answers): AnswerResult
    {
        $rows = $question->metadata['analysis_rows'] ?? [];
        $stored = [];
        $earned = 0;
        $max = 0;

        foreach ($rows as $i => $row) {
            $text = $answers[$i] ?? $answers[(string) $i] ?? null;
            $text = is_string($text) ? trim($text) : '';
            $stored[$i] = $text;

            $points = (int) ($row['points'] ?? 0);
            $max += $points;

            if ($this->rowIsCorrect($row, $text)) {
                $earned += $points;
            }
        }

        return new AnswerResult(
            null,
            json_encode($stored, JSON_UNESCAPED_UNICODE),
            $max > 0 && $earned === $max,
            $earned
        );
    }

    /** Rincian per baris untuk halaman hasil (dipakai SETELAH submit). */
    public function analysisBreakdown(Question $question, ?string $storedJson): array
    {
        $given = json_decode($storedJson ?? '[]', true) ?: [];
        $out = [];

        foreach (($question->metadata['analysis_rows'] ?? []) as $i => $row) {
            $text = (string) ($given[$i] ?? '');
            $ok = $this->rowIsCorrect($row, $text);

            $out[] = [
                'label' => $row['label'] ?? '',
                'answer' => $text,
                'is_correct' => $ok,
                'points' => $ok ? (int) ($row['points'] ?? 0) : 0,
                'max_points' => (int) ($row['points'] ?? 0),
                'expected' => $row['keywords'][0] ?? null,
            ];
        }

        return $out;
    }

    /** lowercase, trim, beberapa spasi jadi satu. */
    public function normalizeAnswer(?string $answer): string
    {
        $answer = mb_strtolower(trim((string) $answer));

        return trim(preg_replace('/\s+/u', ' ', $answer) ?? '');
    }

    /**
     * Kata kunci harus muncul sebagai kata/frasa utuh, bukan potongan kata.
     * "kias" tidak cocok dengan "kiasan".
     */
    public function containsKeyword(string $normalizedAnswer, string $keyword): bool
    {
        $keyword = $this->normalizeAnswer($keyword);

        if ($keyword === '' || $normalizedAnswer === '') {
            return false;
        }

        $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($keyword, '/').'(?![\p{L}\p{N}])/u';

        return preg_match($pattern, $normalizedAnswer) === 1;
    }

    private function matchesQuestion(Question $question, string $normalized): bool
    {
        $exact = ($question->metadata['match'] ?? 'contains') === 'exact';

        $candidates = array_filter(
            array_merge([$question->answer_key], $question->keywords ?? []),
            fn ($k) => is_string($k) && $this->normalizeAnswer($k) !== ''
        );

        foreach ($candidates as $candidate) {
            $matched = $exact
                ? $normalized === $this->normalizeAnswer($candidate)
                : $this->containsKeyword($normalized, $candidate);

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    private function rowIsCorrect(array $row, string $text): bool
    {
        $normalized = $this->normalizeAnswer($text);

        foreach (($row['keywords'] ?? []) as $keyword) {
            if (is_string($keyword) && $this->containsKeyword($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function toIntOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
