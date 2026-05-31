<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Settlement Payment Slip — Ref: {{ $settlement->reference_number ?? 'SR-'.$settlement->id }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #1e293b; line-height: 1.6; }

    /* ── Page header ── */
    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 14px; margin-bottom: 18px; display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; text-align: right; vertical-align: middle; }
    .logo-text { font-size: 20pt; font-weight: bold; color: #7c3aed; }
    .logo-sub  { font-size: 8pt; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 13pt; font-weight: bold; color: #0f172a; }
    .doc-meta  { font-size: 7.5pt; color: #64748b; margin-top: 3px; }

    h2 { font-size: 10pt; font-weight: bold; color: #7c3aed; margin: 16px 0 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.03em; }
    p  { margin-bottom: 5px; }

    /* ── Status badge ── */
    .status-paid       { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }
    .status-approved   { background: #eff6ff; border: 1px solid #3b82f6; color: #1e40af; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }
    .status-processing { background: #faf5ff; border: 1px solid #7c3aed; color: #5b21b6; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }
    .status-pending    { background: #fefce8; border: 1px solid #eab308; color: #713f12; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }
    .status-rejected   { background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 3px 10px; border-radius: 20px; font-size: 8.5pt; font-weight: bold; }

    /* ── Summary box ── */
    .amount-box { background: #f0fdf4; border: 2px solid #10b981; border-radius: 8px; padding: 18px 24px; text-align: center; margin: 16px 0; }
    .amount-label { font-size: 9pt; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .amount-value { font-size: 28pt; font-weight: bold; color: #065f46; }
    .amount-sub   { font-size: 8pt; color: #64748b; margin-top: 4px; }

    /* ── Detail table ── */
    .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .detail-table tr:nth-child(even) { background: #f8fafc; }
    .detail-table td { border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 8.5pt; vertical-align: top; }
    .detail-table td.label { width: 180px; color: #64748b; font-weight: bold; }
    .detail-table td.value { color: #0f172a; }

    /* ── Parties table ── */
    .parties-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .parties-table td { border: 1px solid #e2e8f0; padding: 8px 12px; font-size: 8.5pt; vertical-align: top; width: 50%; }
    .parties-table .party-label { font-size: 7pt; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 3px; }
    .parties-table .party-name  { font-size: 11pt; font-weight: bold; color: #0f172a; }
    .parties-table .party-detail { font-size: 8pt; color: #475569; }

    /* ── Notice box ── */
    .notice-box { background: #fefce8; border: 1px solid #fef08a; border-radius: 4px; padding: 7px 12px; margin: 10px 0; font-size: 8pt; color: #713f12; }

    /* ── Footer ── */
    .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 7pt; color: #94a3b8; text-align: center; }

    /* ── Watermark ── */
    .stamp {
        position: fixed;
        bottom: 60px; right: 40px;
        transform: rotate(-20deg);
        font-size: 34pt;
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

@php
    $ref = $settlement->reference_number ?? 'SR-'.str_pad($settlement->id, 6, '0', STR_PAD_LEFT);
    $statusClass = 'status-'.($settlement->status ?? 'pending');
    $payout = $settlement->payout_details ?? [];
@endphp

<div class="stamp">{{ strtoupper($settlement->status ?? 'PENDING') }}</div>

{{-- ── Header ── --}}
<div class="header">
    <div class="header-left">
        <div class="logo-text">EduBridge</div>
        <div class="logo-sub">by KMG Vital Links (Pvt) Ltd · edu.kmgvitallinks.co.uk</div>
    </div>
    <div class="header-right">
        <div class="doc-title">Settlement Payment Slip</div>
        <div class="doc-meta">Ref: {{ $ref }}</div>
        <div class="doc-meta">Generated {{ now()->format('d M Y, H:i') }} UTC &nbsp;·&nbsp; Status: <span class="{{ $statusClass }}">{{ strtoupper($settlement->status ?? 'PENDING') }}</span></div>
    </div>
</div>

{{-- ── Amount ── --}}
<div class="amount-box">
    <p class="amount-label">Settlement Amount</p>
    <p class="amount-value">USD ${{ number_format((float)$settlement->amount_usd, 2) }}</p>
    <p class="amount-sub">
        Payment Method: {{ strtoupper(str_replace('_', ' ', $settlement->payment_method ?? '—')) }}
        @if($settlement->processed_at)
        &nbsp;·&nbsp; Processed: {{ $settlement->processed_at->format('d M Y') }}
        @endif
    </p>
</div>

{{-- ── Parties ── --}}
<h2>Parties</h2>
<table class="parties-table">
    <tr>
        <td>
            <span class="party-label">Payer (Platform Operator)</span>
            <span class="party-name">KMG Vital Links (Pvt) Ltd</span>
            <div class="party-detail">
                Harare, Zimbabwe<br>
                legal@kmgvitallinks.co.uk<br>
                edu.kmgvitallinks.co.uk
            </div>
        </td>
        <td>
            <span class="party-label">Payee (Independent Contractor)</span>
            <span class="party-name">{{ $settlement->teacher->name }}</span>
            <div class="party-detail">
                Email: {{ $settlement->teacher->email }}<br>
                Teacher ID: #{{ $settlement->teacher_id }}
            </div>
        </td>
    </tr>
</table>

{{-- ── Settlement Details ── --}}
<h2>Settlement Details</h2>
<table class="detail-table">
    <tr>
        <td class="label">Reference Number</td>
        <td class="value" style="font-family: DejaVu Sans Mono, monospace;">{{ $ref }}</td>
    </tr>
    <tr>
        <td class="label">Amount (USD)</td>
        <td class="value"><strong>${{ number_format((float)$settlement->amount_usd, 2) }}</strong></td>
    </tr>
    <tr>
        <td class="label">Status</td>
        <td class="value"><span class="{{ $statusClass }}">{{ ucfirst($settlement->status ?? 'pending') }}</span></td>
    </tr>
    <tr>
        <td class="label">Payment Method</td>
        <td class="value">{{ ucwords(str_replace('_', ' ', $settlement->payment_method ?? '—')) }}</td>
    </tr>
    <tr>
        <td class="label">Submitted On</td>
        <td class="value">{{ optional($settlement->created_at)->format('d M Y, H:i') }} UTC</td>
    </tr>
    @if($settlement->processed_at)
    <tr>
        <td class="label">Processed On</td>
        <td class="value">{{ $settlement->processed_at->format('d M Y, H:i') }} UTC</td>
    </tr>
    @endif
    @if($settlement->processor)
    <tr>
        <td class="label">Processed By</td>
        <td class="value">{{ $settlement->processor->name }}</td>
    </tr>
    @endif
</table>

{{-- ── Payout Destination ── --}}
<h2>Payout Destination</h2>
<table class="detail-table">
    @if(!empty($payout['account_name']))
    <tr>
        <td class="label">Account Name</td>
        <td class="value">{{ $payout['account_name'] }}</td>
    </tr>
    @endif
    @if(!empty($payout['bank_name']))
    <tr>
        <td class="label">Bank Name</td>
        <td class="value">{{ $payout['bank_name'] }}</td>
    </tr>
    @endif
    @if(!empty($payout['account_number']))
    <tr>
        <td class="label">Account Number</td>
        <td class="value" style="font-family: DejaVu Sans Mono, monospace;">{{ $payout['account_number'] }}</td>
    </tr>
    @endif
    @if(!empty($payout['email']))
    <tr>
        <td class="label">Email / Wallet</td>
        <td class="value">{{ $payout['email'] }}</td>
    </tr>
    @endif
</table>

{{-- ── Notes ── --}}
@if($settlement->teacher_notes)
<h2>Teacher Notes</h2>
<p style="font-size:8.5pt;color:#475569;">{{ $settlement->teacher_notes }}</p>
@endif

@if($settlement->admin_notes)
<h2>Admin Notes</h2>
<p style="font-size:8.5pt;color:#475569;">{{ $settlement->admin_notes }}</p>
@endif

{{-- ── Notice ── --}}
<div class="notice-box">
    <strong>Important:</strong> This payment slip is issued by KMG Vital Links (Pvt) Ltd as confirmation of the settlement request above.
    The Contractor is solely responsible for reporting this income to the applicable tax authority.
    Retain this document for your records.
</div>

{{-- ── Footer ── --}}
<div class="footer">
    <p>KMG Vital Links (Pvt) Ltd &nbsp;·&nbsp; Harare, Zimbabwe &nbsp;·&nbsp; legal@kmgvitallinks.co.uk &nbsp;·&nbsp; edu.kmgvitallinks.co.uk</p>
    <p style="margin-top:3px;">Document Reference: {{ $ref }} &nbsp;·&nbsp; Generated {{ now()->format('d M Y H:i') }} UTC &nbsp;·&nbsp; This document was generated automatically by EduBridge.</p>
</div>

</body>
</html>
