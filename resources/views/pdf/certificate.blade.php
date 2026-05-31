<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Certificate of Completion — {{ $certificate->student->name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.5; }

    /* ── Page header ── */
    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 14px; margin-bottom: 24px; display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .logo-text { font-size: 20pt; font-weight: bold; color: #7c3aed; }
    .logo-sub  { font-size: 8pt; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 11pt; font-weight: bold; color: #0f172a; }
    .doc-meta  { font-size: 7.5pt; color: #64748b; margin-top: 3px; }

    /* ── Certificate body ── */
    .cert-wrapper {
        border: 3px solid #7c3aed;
        border-radius: 8px;
        padding: 0;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .cert-band-top    { background: linear-gradient(to right, #7c3aed, #10b981); height: 10px; }
    .cert-band-bottom { background: linear-gradient(to right, #10b981, #7c3aed); height: 10px; }
    .cert-inner { padding: 36px 50px; text-align: center; }

    .cert-org   { font-size: 8.5pt; font-weight: bold; letter-spacing: 0.25em; text-transform: uppercase; color: #94a3b8; margin-bottom: 10px; }
    .cert-title { font-size: 18pt; font-weight: bold; color: #7c3aed; margin-bottom: 6px; letter-spacing: 0.03em; }
    .cert-sub   { font-size: 9pt; color: #64748b; margin-bottom: 20px; }

    .cert-certifies { font-size: 9.5pt; color: #64748b; margin-bottom: 8px; }
    .cert-name  { font-size: 22pt; font-weight: bold; color: #0f172a; margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; display: inline-block; min-width: 280px; }
    .cert-completed { font-size: 9.5pt; color: #64748b; margin-bottom: 8px; }
    .cert-course { font-size: 14pt; font-weight: bold; color: #10b981; margin-bottom: 24px; }

    /* ── Meta grid ── */
    .meta-grid { display: table; width: 100%; border-collapse: collapse; margin: 20px 0; }
    .meta-cell { display: table-cell; text-align: center; padding: 10px 6px; border: 1px solid #e2e8f0; background: #f8fafc; }
    .meta-label { font-size: 7pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 3px; letter-spacing: 0.05em; }
    .meta-value { font-size: 9pt; font-weight: bold; color: #0f172a; }

    /* ── Score badge ── */
    .score-badge { display: inline-block; background: #ecfdf5; border: 2px solid #10b981; border-radius: 50%; width: 72px; height: 72px; line-height: 68px; font-size: 16pt; font-weight: bold; color: #065f46; margin: 16px 0; }

    /* ── Seal ── */
    .seal-row { display: table; width: 100%; margin-top: 24px; }
    .seal-left  { display: table-cell; text-align: left; vertical-align: bottom; width: 50%; padding: 0 20px; }
    .seal-right { display: table-cell; text-align: right; vertical-align: bottom; width: 50%; padding: 0 20px; }
    .sig-line { border-top: 1.5px solid #cbd5e1; padding-top: 4px; margin-top: 36px; font-size: 7.5pt; color: #64748b; }
    .sig-name  { font-size: 9pt; font-weight: bold; color: #1e293b; }

    /* ── Verify strip ── */
    .verify-strip { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 8px 14px; text-align: center; margin: 10px 0 16px; font-size: 8pt; color: #065f46; }
    .verify-strip .cert-num { font-family: DejaVu Sans Mono, monospace; font-weight: bold; letter-spacing: 0.05em; }

    /* ── Footer ── */
    .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 7pt; color: #94a3b8; text-align: center; }

    /* ── Watermark ── */
    .stamp {
        position: fixed;
        bottom: 60px; right: 40px;
        transform: rotate(-20deg);
        font-size: 32pt;
        font-weight: bold;
        color: rgba(16,185,129,0.09);
        border: 5px solid rgba(16,185,129,0.09);
        padding: 5px 12px;
        border-radius: 6px;
        pointer-events: none;
    }
</style>
</head>
<body>

<div class="stamp">CERTIFIED</div>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">EduBridge</div>
        <div class="logo-sub">by KMG Vital Links (Pvt) Ltd · edu.kmgvitallinks.co.uk</div>
    </div>
    <div class="header-right">
        <div class="doc-title">Certificate of Completion</div>
        <div class="doc-meta">Ref: {{ $certificate->certificate_number }}</div>
        <div class="doc-meta">Generated {{ now()->format('d M Y, H:i') }} UTC</div>
    </div>
</div>

{{-- ── Certificate ── --}}
<div class="cert-wrapper">
    <div class="cert-band-top"></div>
    <div class="cert-inner">

        <p class="cert-org">EduBridge Zimbabwe · KMG Vital Links (Pvt) Ltd</p>
        <h1 class="cert-title">Certificate of Completion</h1>
        <p class="cert-sub">This certificate is awarded in recognition of successful course completion.</p>

        <p class="cert-certifies">This is to certify that</p>
        <div>
            <span class="cert-name">{{ $certificate->student->name }}</span>
        </div>
        <p class="cert-completed" style="margin-top:12px;">has successfully completed the course</p>
        <div class="cert-course">{{ $certificate->course->title }}</div>

        @if($certificate->final_score)
        <div style="text-align:center;">
            <div class="score-badge">{{ $certificate->final_score }}%</div>
            <p style="font-size:8pt;color:#64748b;margin-top:4px;">Final Score</p>
        </div>
        @endif

        {{-- Meta grid --}}
        <table class="meta-grid">
            <tr>
                <td class="meta-cell">
                    <span class="meta-label">Certificate No.</span>
                    <span class="meta-value" style="font-family: DejaVu Sans Mono, monospace; font-size:8pt;">{{ $certificate->certificate_number }}</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-label">Date Issued</span>
                    <span class="meta-value">{{ $certificate->issued_at->format('d F Y') }}</span>
                </td>
                <td class="meta-cell">
                    <span class="meta-label">Student ID</span>
                    <span class="meta-value">#{{ $certificate->student_id }}</span>
                </td>
                @if($certificate->course->teacher ?? null)
                <td class="meta-cell">
                    <span class="meta-label">Instructor</span>
                    <span class="meta-value">{{ $certificate->course->teacher->name }}</span>
                </td>
                @endif
            </tr>
        </table>

        {{-- Signatures --}}
        <div class="seal-row">
            <div class="seal-left">
                <p class="sig-name">KMG Vital Links (Pvt) Ltd</p>
                <p class="sig-line">Authorised Signatory, EduBridge</p>
            </div>
            <div class="seal-right">
                <p class="sig-name">{{ $certificate->student->name }}</p>
                <p class="sig-line">Certificate Recipient</p>
            </div>
        </div>

    </div>
    <div class="cert-band-bottom"></div>
</div>

{{-- ── Verification strip ── --}}
<div class="verify-strip">
    ✅ This certificate can be independently verified at
    <strong>edu.kmgvitallinks.co.uk/verify/<span class="cert-num">{{ $certificate->certificate_number }}</span></strong>
</div>

{{-- ── Footer ── --}}
<div class="footer">
    <p>KMG Vital Links (Pvt) Ltd &nbsp;·&nbsp; Harare, Zimbabwe &nbsp;·&nbsp; legal@kmgvitallinks.co.uk &nbsp;·&nbsp; edu.kmgvitallinks.co.uk</p>
    <p style="margin-top:3px;">Certificate Ref: {{ $certificate->certificate_number }} &nbsp;·&nbsp; Generated {{ now()->format('d M Y H:i') }} UTC &nbsp;·&nbsp; This document was generated automatically by EduBridge.</p>
</div>

</body>
</html>
