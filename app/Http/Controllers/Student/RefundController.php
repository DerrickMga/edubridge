<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\RefundRequest;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $requests = $request->user()->refundRequests()->with('payment', 'course')->latest()->paginate(20);
        return view('student.refunds.index', compact('requests'));
    }

    public function create(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        abort_if($payment->status !== 'paid', 422, 'Only paid orders can be refunded.');

        // Per-payment uniqueness: block if already pending/approved
        $existing = RefundRequest::where('payment_id', $payment->id)
            ->whereIn('status', ['pending', 'approved', 'processed'])->first();
        if ($existing) {
            return redirect()->route('student.refunds.index')->with('error', 'Refund already in progress for this payment.');
        }

        return view('student.refunds.create', compact('payment'));
    }

    public function store(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);
        abort_if($payment->status !== 'paid', 422);

        $data = $request->validate(['reason' => 'required|string|min:20|max:2000']);

        RefundRequest::create([
            'user_id'    => $request->user()->id,
            'payment_id' => $payment->id,
            'course_id'  => $payment->course_id,
            'amount'     => $payment->amount,
            'currency'   => $payment->currency,
            'status'     => 'pending',
            'reason'     => $data['reason'],
        ]);

        return redirect()->route('student.refunds.index')->with('success', 'Refund request submitted.');
    }
}
