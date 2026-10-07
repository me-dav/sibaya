<?php

namespace App\Exceptions;

use RuntimeException;

/** Evaluasi tidak dapat dimulai / dijawab. Pesan aman ditampilkan ke siswa. */
class EvaluationNotAvailable extends RuntimeException
{
}
