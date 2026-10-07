<?php

namespace App\Exceptions;

use RuntimeException;

/** Sertifikat tidak boleh diterbitkan. Pesan aman ditampilkan ke siswa. */
class CertificateNotAllowed extends RuntimeException
{
}
