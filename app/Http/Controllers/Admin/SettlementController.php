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
            'pending'    => SettlementRequest::where('status', 'pending')->count(),
            'approved'   => SettlementRequest::where('status', 'approved')->count(),
            'processing' => SettlementRequest::where('status', 'processing')->count(),
            'paid'       => SettlementRequest::where('status', 'paid')->count(),
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
}
