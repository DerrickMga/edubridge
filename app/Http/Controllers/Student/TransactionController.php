<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('course')
            ->latest()
            ->paginate(25);

        $totals = [
            'spent'   => Payment::where('user_id', $request->user()->id)->where('status', 'paid')->sum('amount_usd'),
            'pending' => Payment::where('user_id', $request->user()->id)->where('status', 'pending')->count(),
        ];

        return view('student.transactions', compact('payments', 'totals'));
    }
}
