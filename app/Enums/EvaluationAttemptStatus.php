<?php

namespace App\Enums;

/** Status attempt UTS/UAS. */
enum EvaluationAttemptStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case ABANDONED = 'abandoned';
}
