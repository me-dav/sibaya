<?php

namespace App\Enums;

/** Status attempt Gladhen Mandiri. */
enum AttemptStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case ABANDONED = 'abandoned';
}
