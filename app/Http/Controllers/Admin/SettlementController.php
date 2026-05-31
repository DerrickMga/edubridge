<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SettlementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $settlements = SettlementRequest::with('teacher')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25);

        $counts = [
            'all'        => SettlementRequest::count(),
            'pending'    => SettlementRequest::where('status', 'pending')->count(),
            'approved'   => SettlementRequest::where('status', 'approved')->count(),
            'processing' => SettlementRequest::where('status', 'processing')->count(),
            'paid'       => SettlementRequest::where('status', 'paid')->count(),
            'rejected'   => SettlementRequest::where('status', 'rejected')->count(),
        ];

        $pendingTotal = SettlementRequest::where('status', 'pending')->sum('amount_usd');

        return view('admin.settlements.index', compact('settlements', 'status', 'counts', 'pendingTotal'));
    }

    public function approve(Request $request, SettlementRequest $settlement): RedirectResponse
    {
        abort_if(! in_array($settlement->status, ['pending']), 422);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $settlement->update([
            'status'       => 'approved',
            'admin_notes'  => $data['admin_notes'] ?? null,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Settlement approved.');
    }

    public function markProcessing(Request $request, SettlementRequest $settlement): RedirectResponse
    {
        abort_if($settlement->status !== 'approved', 422);

        $settlement->update([
            'status'       => 'processing',
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Settlement marked as processing.');
    }

    public function markPaid(Request $request, SettlementRequest $settlement): RedirectResponse
    {
        abort_if(! in_array($settlement->status, ['approved', 'processing']), 422);

        $data = $request->validate([
            'reference_number' => ['required', 'string', 'max:100'],
            'admin_notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $settlement->update([
            'status'           => 'paid',
            'reference_number' => $data['reference_number'],
            'admin_notes'      => $data['admin_notes'] ?? null,
            'processed_by'     => $request->user()->id,
            'processed_at'     => now(),
        ]);

        return back()->with('success', "Settlement of \${$settlement->amount_usd} marked as paid (ref: {$data['reference_number']}).");
    }

    public function reject(Request $request, SettlementRequest $settlement): RedirectResponse
    {
        abort_if(! in_array($settlement->status, ['pending', 'approved']), 422);

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $settlement->update([
            'status'       => 'rejected',
            'admin_notes'  => $data['admin_notes'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Settlement request rejected.');
    }

    public function show(SettlementRequest $settlement)
    {
        $settlement->load(['teacher.verification', 'processor']);

        $teacherHistory = SettlementRequest::where('teacher_id', $settlement->teacher_id)
            ->where('id', '!=', $settlement->id)
            ->latest()
            ->take(8)
            ->get();

        return view('admin.settlements.show', compact('settlement', 'teacherHistory'));
    }

    public function reconciliation(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $totals = [
            'paid_all_time'  => SettlementRequest::where('status', 'paid')->sum('amount_usd'),
            'paid_period'    => SettlementRequest::where('status', 'paid')
                                    ->whereBetween('processed_at', [$from.' 00:00:00', $to.' 23:59:59'])
                                    ->sum('amount_usd'),
            'outstanding'    => SettlementRequest::whereIn('status', ['pending','approved','processing'])->sum('amount_usd'),
            'rejected_count' => SettlementRequest::where('status', 'rejected')->count(),
            'total_teachers' => SettlementRequest::distinct('teacher_id')->count('teacher_id'),
        ];

        $teacherBreakdown = SettlementRequest::with('teacher')
            ->selectRaw("teacher_id,
                SUM(CASE WHEN status = 'paid' THEN amount_usd ELSE 0 END) as total_paid,
                SUM(CASE WHEN status IN ('pending','approved','processing') THEN amount_usd ELSE 0 END) as pending_amount,
                COUNT(*) as total_count")
            ->groupBy('teacher_id')
            ->orderByDesc('total_paid')
            ->get();

        $paidSettlements = SettlementRequest::with(['teacher', 'processor'])
            ->where('status', 'paid')
            ->whereBetween('processed_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->orderByDesc('processed_at')
            ->paginate(50);

        return view('admin.settlements.recon', compact('totals', 'teacherBreakdown', 'paidSettlements', 'from', 'to'));
    }
}
