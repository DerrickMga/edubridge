<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $uid = $request->user()->id;

        $payments = Payment::where('user_id', $uid)
            ->with('course')
            ->latest()
            ->paginate(25);

        $totalSpent   = Payment::where('user_id', $uid)->where('status', 'paid')->sum('amount');
        $pendingCount = Payment::where('user_id', $uid)->where('status', 'pending')->count();

        return view('student.transactions', compact('payments', 'totalSpent', 'pendingCount'));
    }
}
