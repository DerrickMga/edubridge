<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>EduBridge Teaching Contract — {{ $contract->teacher->name }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1e293b; line-height: 1.55; }

    /* ── Page header ── */
    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 14px; margin-bottom: 20px; display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .logo-text { font-size: 20pt; font-weight: bold; color: #7c3aed; }
    .logo-sub  { font-size: 8pt; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 14pt; font-weight: bold; color: #0f172a; }
    .doc-meta  { font-size: 8pt; color: #64748b; margin-top: 3px; }

    /* ── Sections ── */
    h2 { font-size: 11pt; font-weight: bold; color: #7c3aed; margin: 18px 0 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    h3 { font-size: 10pt; font-weight: bold; color: #334155; margin: 12px 0 4px; }
    p  { margin-bottom: 6px; }
    ul { padding-left: 18px; margin-bottom: 6px; }
    li { margin-bottom: 3px; }

    /* ── Key terms grid ── */
    .terms-grid { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .terms-grid td { border: 1px solid #e2e8f0; padding: 7px 10px; font-size: 9pt; vertical-align: top; width: 25%; }
    .terms-grid .label { font-size: 7.5pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 2px; }
    .terms-grid .value { font-weight: bold; color: #0f172a; }

    /* ── Policy section ── */
    .policy-box { border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 14px; page-break-inside: avoid; }
    .policy-header { background: #f8fafc; padding: 7px 12px; border-bottom: 1px solid #e2e8f0; }
    .policy-header .policy-title { font-size: 10pt; font-weight: bold; color: #1e293b; }
    .policy-header .policy-meta  { font-size: 7.5pt; color: #94a3b8; }
    .policy-body { padding: 10px 12px; font-size: 9pt; color: #334155; white-space: pre-wrap; word-wrap: break-word; }

    /* ── Signature block ── */
    .sig-box { border: 2px solid #10b981; border-radius: 6px; padding: 14px 16px; margin-top: 24px; background: #f0fdf4; }
    .sig-box .sig-badge { font-size: 13pt; margin-bottom: 6px; }
    .sig-box .sig-name  { font-size: 13pt; font-weight: bold; color: #065f46; margin-bottom: 4px; }
    .sig-box table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .sig-box td { font-size: 8.5pt; padding: 3px 0; vertical-align: top; }
    .sig-box td.label { width: 130px; color: #64748b; }
    .sig-box td.value { color: #0f172a; font-weight: bold; }

    /* ── Footer ── */
    .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 7.5pt; color: #94a3b8; text-align: center; }

    /* ── Watermark for signed ── */
    .signed-stamp {
        position: fixed;
        bottom: 80px; right: 40px;
        transform: rotate(-25deg);
        font-size: 38pt;
        font-weight: bold;
        color: rgba(16,185,129,0.12);
        border: 6px solid rgba(16,185,129,0.12);
        padding: 6px 14px;
        border-radius: 6px;
        pointer-events: none;
    }
</style>
</head>
<body>

<div class="signed-stamp">SIGNED</div>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">EduBridge</div>
        <div class="logo-sub">by KMG Vital Links (Pvt) Ltd · edu.kmgvitallinks.co.uk</div>
    </div>
    <div class="header-right">
        <div class="doc-title">Teaching Contract</div>
        <div class="doc-meta">Version {{ $contract->version }} · Generated {{ now()->format('d M Y, H:i') }} UTC</div>
    </div>
</div>

{{-- ── Parties ── --}}
<h2>1. Parties</h2>
<table class="terms-grid">
    <tr>
        <td><span class="label">Platform Operator</span><span class="value">KMG Vital Links (Pvt) Ltd</span></td>
        <td><span class="label">Teacher / Contractor</span><span class="value">{{ $contract->teacher->name }}</span></td>
        <td><span class="label">Teacher Email</span><span class="value">{{ $contract->teacher->email }}</span></td>
        <td><span class="label">Contract Status</span><span class="value">{{ strtoupper($contract->status) }}</span></td>
    </tr>
</table>

{{-- ── Key Terms ── --}}
<h2>2. Key Contract Terms</h2>
<table class="terms-grid">
    <tr>
        <td><span class="label">Hourly Rate (USD)</span><span class="value">${{ number_format((float)($contract->rate_usd ?? 0), 2) }}</span></td>
        <td><span class="label">Payment Terms</span><span class="value">{{ $contract->payment_terms ?? '—' }}</span></td>
        <td><span class="label">Contract Term</span><span class="value">{{ $contract->term_months ? $contract->term_months.' months' : '—' }}</span></td>
        <td><span class="label">Exclusivity</span><span class="value">{{ str_replace('_', '-', $contract->exclusivity ?? '—') }}</span></td>
    </tr>
    <tr>
        <td><span class="label">Issued</span><span class="value">{{ optional($contract->issued_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Effective From</span><span class="value">{{ optional($contract->effective_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Expires</span><span class="value">{{ optional($contract->expires_at)->format('d M Y') ?? '—' }}</span></td>
        <td><span class="label">Contract Version</span><span class="value">v{{ $contract->version }}</span></td>
    </tr>
</table>

@if($contract->addendum)
<h2>3. Addendum / Special Conditions</h2>
<p>{{ $contract->addendum }}</p>
@endif

{{-- ── Incorporated Policies ── --}}
<h2>{{ $contract->addendum ? '4' : '3' }}. Policies Incorporated by Reference</h2>
<p style="font-size:9pt;color:#64748b;margin-bottom:10px;">
    The following policies were in force at the time this contract was signed. The full text of each policy as accepted is reproduced below.
</p>

@foreach($policies as $policy)
<div class="policy-box">
    <div class="policy-header">
        <span class="policy-title">{{ $policy->title }}</span>
        <span class="policy-meta"> &nbsp;·&nbsp; v{{ $policy->version }} &nbsp;·&nbsp; {{ str_replace('_', ' ', $policy->category) }} &nbsp;·&nbsp; Effective {{ optional($policy->effective_at)->format('d M Y') }}</span>
    </div>
    <div class="policy-body">{{ $policy->body }}</div>
</div>
@endforeach

{{-- ── Signature Block ── --}}
<div class="sig-box">
    <div class="sig-badge">✅ Electronically Signed</div>
    <div class="sig-name">{{ $contract->signed_name }}</div>
    <table>
        <tr>
            <td class="label">Full Legal Name</td>
            <td class="value">{{ $contract->signed_name }}</td>
        </tr>
        <tr>
            <td class="label">Signed On</td>
            <td class="value">{{ $contract->signed_at?->format('d M Y, H:i') }} UTC</td>
        </tr>
        <tr>
            <td class="label">IP Address</td>
            <td class="value">{{ $contract->signed_ip ?? 'not recorded' }}</td>
        </tr>
        <tr>
            <td class="label">Contract Expires</td>
            <td class="value">{{ optional($contract->expires_at)->format('d M Y') ?? 'No expiry set' }}</td>
        </tr>
        <tr>
            <td class="label">Document Reference</td>
            <td class="value">EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</td>
        </tr>
    </table>
    <p style="font-size:8pt;color:#065f46;margin-top:10px;">
        This electronic signature constitutes a legally binding agreement between the teacher named above and KMG Vital Links (Pvt) Ltd under the laws of Zimbabwe. The signing metadata above provides a complete audit trail.
    </p>
</div>

{{-- ── Footer ── --}}
<div class="footer">
    <p>KMG Vital Links (Pvt) Ltd &nbsp;·&nbsp; Harare, Zimbabwe &nbsp;·&nbsp; legal@kmgvitallinks.co.uk &nbsp;·&nbsp; edu.kmgvitallinks.co.uk</p>
    <p style="margin-top:3px;">This document was generated automatically by EduBridge. Document reference: EDUBR-CONTRACT-{{ str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}-V{{ $contract->version }}</p>
</div>

</body>
</html>
