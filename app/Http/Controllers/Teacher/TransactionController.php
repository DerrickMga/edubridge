<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SettlementRequest;
use App\Models\TeacherPaymentItem;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // All payment items (sessions logged)
        $paymentItems = TeacherPaymentItem::where('teacher_id', $user->id)
            ->with(['session', 'sessionLog'])
            ->latest()
            ->paginate(30, ['*'], 'earnings_page');

        // All settlement requests
        $settlements = SettlementRequest::where('teacher_id', $user->id)
            ->latest()
            ->paginate(20, ['*'], 'settlements_page');

        // Summary totals
        $totals = [
            'earned'    => TeacherPaymentItem::where('teacher_id', $user->id)->whereIn('status', ['approved', 'paid'])->sum('total_usd'),
            'pending'   => TeacherPaymentItem::where('teacher_id', $user->id)->where('status', 'pending')->sum('total_usd'),
            'settled'   => SettlementRequest::where('teacher_id', $user->id)->where('status', 'paid')->sum('amount_usd'),
            'in_flight' => SettlementRequest::where('teacher_id', $user->id)->whereIn('status', ['pending', 'approved', 'processing'])->sum('amount_usd'),
        ];
        $totals['available'] = max(0, $totals['earned'] - $totals['settled'] - $totals['in_flight']);

        return view('teacher.transactions.index', compact('paymentItems', 'settlements', 'totals'));
    }
}
