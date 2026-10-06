<?php

namespace App\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';
    case AUDIO_CHOICE = 'audio_choice';
    case TEXT_ANALYSIS = 'text_analysis';
    case SHORT_ANSWER = 'short_answer';

    /** Tipe yang memakai opsi A-D. */
    public function hasOptions(): bool
    {
        return in_array($this, [self::MULTIPLE_CHOICE, self::AUDIO_CHOICE], true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
