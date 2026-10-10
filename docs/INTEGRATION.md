# siBAYA — Panduan Integrasi Tahap 3–12

Folder ini adalah **overlay**: berisi **versi terbaru** dari setiap file Tahap 3–11 (file yang di-patch
pada tahap berikutnya sudah memuat patch itu), ditambah perbaikan Tahap 12. Salin isinya ke root project
Laravel 12 Anda — tidak perlu lagi menerapkan zip per tahap satu per satu.

> Semua kode di sini ditulis tanpa kemampuan menjalankan PHP. Belum pernah dieksekusi. Ikuti urutan
> verifikasi di bagian 4 dan perlakukan kegagalan pertama sebagai informasi, bukan kejutan.

## 1. Menerapkan

```bash
# dari root project Laravel
cp -r /path/ke/sibaya-overlay/. .

composer require barryvdh/laravel-dompdf        # wajib (sertifikat PDF)
composer require simplesoftwareio/simple-qrcode # opsional (QR code di sertifikat)
npm install && npm run build                    # atau npm run dev
php artisan storage:link
php artisan migrate                             # 3 migration baru: puzzles, puzzle_attempts, indeks performa
php artisan optimize:clear
```

Lalu **gabungkan secara manual** (jangan menimpa file Anda):

| File Anda | Ambil dari | Catatan |
|---|---|---|
| `routes/web.php` | `routes/web.php.EXAMPLE` | Pertahankan `require __DIR__.'/auth.php'`. Route Tahap 5 (Gladhen) milik Anda. |
| `routes/admin.php` | `routes/admin.php.EXAMPLE` | Daftarkan di `bootstrap/app.php` (petunjuk di kepala file). |
| `routes/console.php` | `routes/console.php.EXAMPLE` | Jadwal `evaluations:expire`. |
| `config/sibaya.php` | (sudah di overlay) | Tambahkan kunci opsional: `private_disk`, `certificate.*` (lihat bawah). |
| `.env` / `.env.example` | bawah | Variabel sertifikat. |
| `app/Models/User.php` | — | Pastikan relasi `exerciseAttempts()`, `evaluationAttempts()`; opsional `puzzleAttempts()`. |
| `app/Policies/*` | overlay | Jika Anda sudah punya `ExercisePolicy` / `UserPolicy` dari tahap lain, **gabungkan** method-nya. |
| `x-admin-sidebar` | — | Tambahkan link: Siswa, Materi, Latihan, Hasil, Evaluasi, Puzzle. |

`config/sibaya.php` tambahan:

```php
'private_disk' => env('SIBAYA_PRIVATE_DISK', 'local'),
'certificate'  => [
    'institution'  => env('CERTIFICATE_INSTITUTION', 'SiBAYA - Sinau Basa lan Budaya Jawa'),
    'signer_name'  => env('CERTIFICATE_SIGNER_NAME', ''),
    'signer_title' => env('CERTIFICATE_SIGNER_TITLE', 'Admin SiBAYA'),
],
```

## 2. Kontrak dengan bagian yang dikerjakan di luar paket ini

Kode Tahap 3–11 mengasumsikan hal berikut dari **Tahap 1, 2, 5, 6**. `ContractTest` memeriksa semuanya
otomatis — jalankan itu lebih dulu.

* **Tabel & kolom** persis `siBAYA.md` bagian 19 (termasuk `puzzles` dan `puzzle_attempts` dari Tahap 11).
* **Model** `User, Material, RinenggaType, Exercise, Question, QuestionOption, ExerciseAttempt, ExerciseAnswer,
  Evaluation, EvaluationAttempt, EvaluationAnswer, Recommendation, Certificate` dengan relasi:
  `Material::rinenggaType/recommendations`, `RinenggaType::materials`, `Exercise::questions`, `Question::options`,
  `ExerciseAttempt::user/exercise`, `ExerciseAnswer::question/selectedOption`, `EvaluationAttempt::user/evaluation`,
  `User::exerciseAttempts/evaluationAttempts`.
* **Enum** `UserRole (ADMIN, SISWA)`, `ExerciseType`, `QuestionType`, `EvaluationType`.
* **`AnswerEvaluationService`** (Tahap 6) dengan `evaluateMultipleChoice($question, $selectedOptionId)` dan
  `evaluateShortAnswer($question, $answerText)`. Jika berbeda, ubah **hanya** `EvaluationService::grade()`.
* **Layout/komponen**: `<x-layouts.public>`, `<x-layouts.admin>`, `x-input-error`; middleware alias `role`.
* **Disk private** untuk audio soal (`local` atau `SIBAYA_PRIVATE_DISK`).
* Kolom `keywords`/`metadata` pada `questions` boleh di-cast array atau tidak (kode menangani keduanya).

## 3. Isi per tahap

| Tahap | Isi |
|---|---|
| 3 | Beranda, navbar, footer, layout publik |
| 4 | Ruang Piwulang, CRUD materi, Markdown aman, media |
| 7 | Dashboard admin, CRUD siswa, CRUD latihan, form builder soal, hasil latihan |
| 8 | UTS/UAS: bank soal, timer server, autosave, penilaian, hasil |
| 9 | Rekomendasi materi otomatis, dashboard siswa |
| 10 | Sertifikat PDF, halaman cetak, verifikasi publik |
| 11 | Wahana Teka-teki: silang + susun kata |
| 12 | Test kontrak, sapuan otorisasi, indeks DB, perbaikan N+1, route terkonsolidasi, dokumen ini |

## 4. Urutan verifikasi (penting)

```bash
php artisan migrate:fresh --seed     # database dev saja
php artisan test --filter=ContractTest              # 1) asumsi terhadap Tahap 1/2/5/6
php artisan test --filter=AuthorizationSweepTest    # 2) semua route admin menolak tamu & siswa
php artisan test                                    # 3) seluruh suite
php artisan route:list --name=admin | head -80
```

Jika `ContractTest` gagal, perbaiki **itu dulu**; kegagalan lain biasanya turunan darinya.
Urutan kecurigaan untuk kegagalan test fitur: (1) signature `AnswerEvaluationService`, (2) nama relasi model,
(3) nama layout/komponen, (4) tipe kolom `score` pada migration Tahap 2.

## 5. QA manual (tidak bisa diotomatisasi di sini)

**Responsif** — uji pada lebar 360, 768, 1280 px: Beranda, Ruang Piwulang (sidebar → `<details>` di mobile),
detail materi, form pengerjaan evaluasi (header timer sticky), grid teka-teki silang (scroll horizontal),
tabel admin (scroll horizontal), halaman cetak sertifikat (Ctrl+P, landscape).

**Aksesibilitas** — keyboard saja: (a) lewati navbar dengan "Lompat ke konten"; (b) kerjakan satu evaluasi
tanpa mouse; (c) susun kata memakai Tab/Enter; (d) silang memakai panah/Backspace; (e) pastikan fokus selalu terlihat.
Screen reader: timer (`role=timer`), status benar/salah memakai teks + ikon (bukan warna saja), label semua input.
Kontras: periksa teks emas (#E2AC2F) di atas navy dan `text-slate-500` di atas putih (batas AA 4.5:1 untuk teks kecil).
`prefers-reduced-motion` sudah dihormati pada `app.css`.

**Fungsional lintas fitur** — (1) siswa lulus UTS → rekomendasi muncul → klik menandai dibaca;
(2) lulus → terbitkan sertifikat → unduh PDF → buka tautan verifikasi tanpa login;
(3) buat TTS 4 kata dari admin → mainkan → skor tersimpan;
(4) tutup tab saat evaluasi berjalan → buka lagi setelah durasi habis → status `expired`, nilai dari jawaban tersimpan.

## 6. Tinjauan keamanan (hasil audit statis)

| Area | Hasil |
|---|---|
| CSRF | Semua `<form method="POST">` memuat `@csrf` (dipindai otomatis) |
| XSS | Tidak ada `{!! !!}` di seluruh Blade; Markdown memakai `html_input=strip` + `allow_unsafe_links=false` |
| Mass assignment | Data selalu dari Form Request + `forceFill`; model Puzzle memakai `$fillable` eksplisit |
| IDOR | Policy `denyAsNotFound` untuk attempt evaluasi, attempt puzzle, rekomendasi, sertifikat; test untuk tiap kasus |
| Otorisasi admin | `AuthorizationSweepTest` menyapu semua route `admin.*` (tamu → login, siswa → 403) |
| Nilai/timer | Dihitung dan divalidasi di server; skor dari browser diabaikan (ada test) |
| Kunci jawaban | Tidak dikirim ke browser saat mengerjakan (ada test); tidak ditampilkan setelah submit pada evaluasi/puzzle |
| Upload | Validasi mimes + ukuran; nama file acak; audio soal di disk private + stream terotorisasi |
| Sertifikat | Disk private, unduh terotorisasi, verifikasi publik hanya data minimum, nomor ber-akhiran acak |
| Rate limit | Route tambahan diberi `throttle` pada `web.php.EXAMPLE` (login sudah ditangani Breeze) |
| Tidak diaudit / perlu Anda | Header keamanan (CSP, HSTS), pemindaian virus upload, `APP_DEBUG=false` di produksi, backup DB, rotasi log |

## 7. Keputusan desain & pertanyaan terbuka (ringkasan lintas tahap)

* Rincian jawaban puzzle hanya tampil sekali (skema tidak punya kolom jawaban). Perlu kolom baru jika ingin permanen.
* Kunci jawaban tidak ditampilkan setelah evaluasi/puzzle (cegah kebocoran antar percobaan).
* Admin tidak dapat mengerjakan evaluasi/puzzle (menjaga data siswa bersih).
* Satu sertifikat per attempt (constraint skema); daftar menampilkan attempt lulus terbaik per evaluasi.
* Batas predikat sertifikat 90/80 adalah keputusan sementara (`CertificateService::predicateFor`).
* Belum dibuat: pembuatan akun admin lain (bagian 7.2), rekomendasi manual oleh admin, halaman admin sertifikat,
  `/admin/statistics`, leaderboard, aktivitas pengguna.
