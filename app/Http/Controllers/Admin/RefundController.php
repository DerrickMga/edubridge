<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $q = RefundRequest::with('user', 'payment', 'course')->latest();
        if ($status && in_array($status, RefundRequest::statuses(), true)) {
            $q->where('status', $status);
        }
        $requests = $q->paginate(25)->withQueryString();
        $counts = [
            'pending'   => RefundRequest::where('status', 'pending')->count(),
            'approved'  => RefundRequest::where('status', 'approved')->count(),
            'rejected'  => RefundRequest::where('status', 'rejected')->count(),
            'processed' => RefundRequest::where('status', 'processed')->count(),
        ];
        return view('admin.refunds.index', compact('requests', 'counts', 'status'));
    }

    public function update(Request $request, RefundRequest $refund)
    {
        $data = $request->validate([
            'status'      => ['required', Rule::in(RefundRequest::statuses())],
            'admin_notes' => 'nullable|string|max:2000',
        ]);
        $refund->update($data + [
            'decided_by' => $request->user()->id,
            'decided_at' => now(),
        ]);
        return back()->with('success', 'Refund updated.');
    }
}
