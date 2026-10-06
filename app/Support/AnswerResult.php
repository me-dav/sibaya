<?php

namespace App\Support;

/** Hasil penilaian satu jawaban. Dihitung di server, tidak pernah dari frontend. */
final readonly class AnswerResult
{
    public function __construct(
        public ?int $selectedOptionId,
        public ?string $answerText,
        public bool $isCorrect,
        public int $pointsEarned,
    ) {}
}
