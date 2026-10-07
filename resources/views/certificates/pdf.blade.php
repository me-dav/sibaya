<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Sertifikat {{ $number }}</title>
<style>
    @page { margin: 0; }
    body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: #14274A; }
    /* A4 landscape = 1123 x 794 px pada 96 dpi */
    .frame { position: absolute; top: 20px; left: 20px; width: 1071px; height: 742px; border: 6px solid #12376B; }
    .inner { position: absolute; top: 8px; left: 8px; width: 1051px; height: 722px; border: 2px solid #E2AC2F; text-align: center; }
    .institution { margin-top: 34px; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; color: #1B4A8A; }
    .title { margin-top: 10px; font-size: 46px; font-weight: bold; letter-spacing: 12px; color: #12376B; }
    .muted { font-size: 13px; color: #555; }
    .name { margin: 8px auto 0; width: 700px; padding-bottom: 6px; border-bottom: 2px solid #E2AC2F; font-size: 36px; font-weight: bold; color: #0A2A55; }
    .eval { margin-top: 6px; font-size: 24px; font-weight: bold; color: #12376B; }
    table.score { margin: 22px auto 0; border-collapse: separate; border-spacing: 12px 0; }
    table.score td { width: 150px; padding: 10px 6px; background: #E8F0FC; font-size: 12px; color: #555; }
    table.score td strong { display: block; margin-top: 4px; font-size: 20px; color: #14274A; }
    .footer { position: absolute; left: 40px; bottom: 28px; width: 971px; }
    table.footer-table { width: 100%; border-collapse: collapse; }
    table.footer-table td { vertical-align: bottom; font-size: 12px; }
    .sign-line { margin: 44px auto 0; width: 220px; padding-top: 4px; border-top: 1px solid #555; font-weight: bold; }
</style>
</head>
<body>
<div class="frame"><div class="inner">
    <div class="institution">{{ $institution }}</div>
    <div class="title">SERTIFIKAT</div>
    <p class="muted" style="margin: 18px 0 0;">Diparingake marang</p>
    <div class="name">{{ $name }}</div>
    <p class="muted" style="margin: 8px 0 0;">{{ $school }}@if ($class) &middot; Kelas {{ $class }}@endif</p>

    <p style="margin: 22px 0 0; font-size: 14px;">kang wis ngrampungake lan dinyatakake <strong style="color:#1B7A3C;">LULUS</strong> ing</p>
    <div class="eval">{{ $evaluation }} ({{ $type }})</div>

    <table class="score">
        <tr>
            <td>Nilai<strong>{{ number_format((float) $score, 1, ',', '.') }}</strong></td>
            <td>Predikat<strong>{{ $predicate }}</strong></td>
            <td>Status<strong style="color:#1B7A3C;">LULUS</strong></td>
        </tr>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="text-align:left; width:380px;">
                    Diterbitake: <strong>{{ $issuedAt }}</strong><br>
                    Nomor: <strong>{{ $number }}</strong><br>
                    <span style="font-size:10px; color:#666;">Verifikasi: {{ $verifyUrl }}</span>
                </td>
                <td style="text-align:center;">
                    {{ $signerTitle }}
                    <div class="sign-line">{{ $signerName ?: '..............................' }}</div>
                </td>
                <td style="text-align:right; width:130px;">
                    @if ($qr)<img src="{{ $qr }}" width="110" height="110" alt="QR verifikasi">@endif
                </td>
            </tr>
        </table>
    </div>
</div></div>
</body>
</html>
