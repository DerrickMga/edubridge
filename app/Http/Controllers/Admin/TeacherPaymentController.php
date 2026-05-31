<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherPaymentItem;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherPaymentItem::with(['teacher', 'session.course', 'sessionLog', 'approver'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        $payments = $query->paginate(30)->withQueryString();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get(['id', 'name']);

        $totals = [
            'pending'  => TeacherPaymentItem::where('status', 'pending')->sum('total_usd'),
            'approved' => TeacherPaymentItem::where('status', 'approved')->sum('total_usd'),
            'paid'     => TeacherPaymentItem::where('status', 'paid')->sum('total_usd'),
        ];

        return view('admin.teacher-payments.index', compact('payments', 'teachers', 'totals'));
    }

    public function approve(Request $request, TeacherPaymentItem $payment)
    {
        abort_if($payment->status !== 'pending', 422, 'Only pending payments can be approved.');

        $data = $request->validate([
            'admin_notes'     => ['nullable', 'string', 'max:1000'],
            'hourly_rate_usd' => ['nullable', 'numeric', 'min:0', 'max:9999'],
        ]);

        // Allow admin to override rate at approval time
        if (! empty($data['hourly_rate_usd'])) {
            $payment->hourly_rate_usd = (float) $data['hourly_rate_usd'];
            $payment->total_usd       = round($payment->hours_logged * $payment->hourly_rate_usd, 2);
        }

        $payment->status      = 'approved';
        $payment->admin_notes = $data['admin_notes'] ?? null;
        $payment->approved_by = $request->user()->id;
        $payment->approved_at = now();
        $payment->save();

        return back()->with('success', 'Payment approved: $' . number_format($payment->total_usd, 2) . ' for ' . $payment->teacher->name);
    }

    public function reject(Request $request, TeacherPaymentItem $payment)
    {
        abort_if(! in_array($payment->status, ['pending', 'approved']), 422, 'Cannot reject a paid payment.');

        $data = $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        $payment->update([
            'status'      => 'pending',
            'admin_notes' => $data['admin_notes'],
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', 'Payment returned to pending.');
    }

    public function markPaid(Request $request, TeacherPaymentItem $payment)
    {
        abort_if($payment->status !== 'approved', 422, 'Only approved payments can be marked paid.');

        $payment->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Payment of $' . number_format($payment->total_usd, 2) . ' marked as paid.');
    }

    /**
     * Update a teacher's hourly rate from the admin payments screen.
     */
    public function updateRate(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 422);
        $data = $request->validate(['hourly_rate_usd' => ['required', 'numeric', 'min:0', 'max:9999']]);
        $teacher->update(['hourly_rate_usd' => $data['hourly_rate_usd']]);

        return back()->with('success', $teacher->name . "'s hourly rate updated to $" . number_format($data['hourly_rate_usd'], 2));
    }
}
