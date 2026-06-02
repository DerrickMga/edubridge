<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SettlementRequest;
use App\Models\TeacherPaymentItem;
use App\Services\SettlementFeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Total approved earnings
        $totalEarned = TeacherPaymentItem::where('teacher_id', $user->id)
            ->whereIn('status', ['approved', 'paid'])->sum('total_usd');

        // Total already settled (paid-out)
        $totalSettled = SettlementRequest::where('teacher_id', $user->id)
            ->where('status', 'paid')->sum('amount_usd');

        // Pending settlement amounts
        $pendingSettled = SettlementRequest::where('teacher_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'processing'])->sum('amount_usd');

        $availableBalance = max(0, $totalEarned - $totalSettled - $pendingSettled);

        $settlements = SettlementRequest::where('teacher_id', $user->id)
            ->latest()->paginate(20);

        // Get teacher's verification payout details
        $verification = $user->verification;

        $paymentMethods = SettlementFeeService::$labels;
        $settlementFees = SettlementFeeService::$fees;

        return view('teacher.settlements.index', compact(
            'settlements', 'totalEarned', 'totalSettled', 'pendingSettled',
            'availableBalance', 'verification',
            'paymentMethods', 'settlementFees'
        ));
    }

    public function downloadPdf(Request $request, SettlementRequest $settlement)
    {
        // Only allow the owning teacher to download their own slip
        abort_unless($settlement->teacher_id === $request->user()->id, 403);

        $settlement->load(['teacher', 'processor']);
        $ref = $settlement->reference_number ?? 'SR-'.str_pad($settlement->id, 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('pdf.settlement', compact('settlement'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("EduBridge-Settlement-{$ref}.pdf");
    }

    public function store(Request $request): RedirectResponse
    {        $user = $request->user();

        // Must be verified
        if (! $user->is_verified) {
            return back()->with('error', 'Your account must be verified before requesting a settlement. Please complete your verification first.');
        }

        $data = $request->validate([
            'amount_usd'     => ['required', 'numeric', 'min:5', 'max:50000'],
            'payment_method' => ['required', 'in:omari,paystack,bank_transfer,ecocash,innbucks,paynow,cash_token'],
            'payout_details' => ['required', 'array'],
            'payout_details.account_name'   => ['required', 'string', 'max:255'],
            'payout_details.account_number' => ['required_if:payment_method,bank_transfer,ecocash,innbucks,omari,cash_token', 'nullable', 'string', 'max:50'],
            'payout_details.bank_name'      => ['required_if:payment_method,bank_transfer,paystack', 'nullable', 'string', 'max:100'],
            'payout_details.email'          => ['required_if:payment_method,paynow,paystack', 'nullable', 'email'],
            'teacher_notes'  => ['nullable', 'string', 'max:500'],
        ]);

        // Check available balance
        $totalEarned   = TeacherPaymentItem::where('teacher_id', $user->id)->whereIn('status', ['approved', 'paid'])->sum('total_usd');
        $totalSettled  = SettlementRequest::where('teacher_id', $user->id)->where('status', 'paid')->sum('amount_usd');
        $pendingOut    = SettlementRequest::where('teacher_id', $user->id)->whereIn('status', ['pending', 'approved', 'processing'])->sum('amount_usd');
        $available     = max(0, $totalEarned - $totalSettled - $pendingOut);

        if ($data['amount_usd'] > $available) {
            return back()->withErrors(['amount_usd' => "Requested amount exceeds your available balance of \${$available}."])->withInput();
        }

        // Calculate settlement fee
        $fees = SettlementFeeService::calculate((float) $data['amount_usd'], $data['payment_method']);

        SettlementRequest::create([
            'teacher_id'         => $user->id,
            'amount_usd'         => $data['amount_usd'],
            'settlement_fee_pct' => $fees['fee_pct'],
            'settlement_fee_usd' => $fees['fee_usd'],
            'net_amount_usd'     => $fees['net_usd'],
            'payment_method'     => $data['payment_method'],
            'payout_details'     => $data['payout_details'],
            'teacher_notes'      => $data['teacher_notes'] ?? null,
            'status'             => 'pending',
        ]);

        return redirect()->route('teacher.settlements.index')
            ->with('success', 'Settlement request of $' . number_format($data['amount_usd'], 2) . ' submitted (fee: $' . number_format($fees['fee_usd'], 2) . ' — you receive $' . number_format($fees['net_usd'], 2) . '). We\'ll process within 3–5 business days.');
    }
}
