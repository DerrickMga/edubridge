<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentLoanApplication;
use App\Models\TeacherEquipmentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentController extends Controller
{
    /**
     * List all loan applications with status filter + equipment profile overview.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = EquipmentLoanApplication::with(['teacher.equipmentProfile'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $loans = $query->paginate(25)->withQueryString();

        $counts = [
            'all'          => EquipmentLoanApplication::count(),
            'pending'      => EquipmentLoanApplication::where('status', 'pending')->count(),
            'under_review' => EquipmentLoanApplication::where('status', 'under_review')->count(),
            'approved'     => EquipmentLoanApplication::where('status', 'approved')->count(),
            'disbursed'    => EquipmentLoanApplication::where('status', 'disbursed')->count(),
            'repaying'     => EquipmentLoanApplication::where('status', 'repaying')->count(),
            'completed'    => EquipmentLoanApplication::where('status', 'completed')->count(),
            'rejected'     => EquipmentLoanApplication::where('status', 'rejected')->count(),
        ];

        // Equipment profile compliance summary
        $profileStats = [
            'total'        => TeacherEquipmentProfile::count(),
            'ready'        => TeacherEquipmentProfile::where('status', 'meets_requirements')->count(),
            'needs_work'   => TeacherEquipmentProfile::where('status', 'needs_improvement')->count(),
            'incomplete'   => TeacherEquipmentProfile::where('status', 'incomplete')->count(),
            'no_profile'   => User::where('role', 'teacher')->whereDoesntHave('equipmentProfile')->count(),
        ];

        return view('admin.equipment.index', compact('loans', 'counts', 'status', 'profileStats'));
    }

    /**
     * Show a single loan application.
     */
    public function show(EquipmentLoanApplication $loan)
    {
        $loan->load(['teacher.equipmentProfile', 'reviewer']);
        return view('admin.equipment.show', compact('loan'));
    }

    /**
     * Mark application as under_review.
     */
    public function markUnderReview(EquipmentLoanApplication $loan)
    {
        abort_if(! in_array($loan->status, ['pending']), 422);
        $loan->update(['status' => 'under_review', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return back()->with('success', 'Application marked as under review.');
    }

    /**
     * Approve a loan application.
     */
    public function approve(Request $request, EquipmentLoanApplication $loan)
    {
        abort_if(! in_array($loan->status, ['pending', 'under_review']), 422);

        $data = $request->validate([
            'approved_amount_usd'    => ['required', 'numeric', 'min:1', 'max:2000'],
            'approved_months'        => ['required', 'integer', 'in:3,6,9,12,18,24'],
            'interest_rate_percent'  => ['required', 'numeric', 'min:0', 'max:50'],
            'repayment_starts_on'    => ['required', 'date', 'after:today'],
            'admin_notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        $monthlyRepayment = round(
            $data['approved_amount_usd'] * (1 + $data['interest_rate_percent'] / 100) / $data['approved_months'],
            2
        );

        $repaymentEnd = \Carbon\Carbon::parse($data['repayment_starts_on'])
            ->addMonths((int) $data['approved_months'])
            ->toDateString();

        $loan->update(array_merge($data, [
            'status'                => 'approved',
            'reviewed_by'           => auth()->id(),
            'reviewed_at'           => now(),
            'monthly_repayment_usd' => $monthlyRepayment,
            'repayment_ends_on'     => $repaymentEnd,
        ]));

        return back()->with('success', 'Loan application approved. Awaiting disbursement.');
    }

    /**
     * Mark as disbursed (equipment physically handed over / funds sent).
     */
    public function disburse(Request $request, EquipmentLoanApplication $loan)
    {
        abort_if($loan->status !== 'approved', 422);

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $loan->update([
            'status'       => 'disbursed',
            'disbursed_at' => now(),
            'admin_notes'  => $request->admin_notes ?? $loan->admin_notes,
        ]);

        return back()->with('success', 'Marked as disbursed. Repayment schedule is now active.');
    }

    /**
     * Mark as repaying (teacher has started instalments).
     */
    public function markRepaying(EquipmentLoanApplication $loan)
    {
        abort_if($loan->status !== 'disbursed', 422);
        $loan->update(['status' => 'repaying']);
        return back()->with('success', 'Status updated to Repaying.');
    }

    /**
     * Mark as completed (fully repaid).
     */
    public function markCompleted(EquipmentLoanApplication $loan)
    {
        abort_if(! in_array($loan->status, ['repaying', 'disbursed']), 422);
        $loan->update(['status' => 'completed']);
        return back()->with('success', 'Loan marked as fully repaid. Completed.');
    }

    /**
     * Reject a loan application.
     */
    public function reject(Request $request, EquipmentLoanApplication $loan)
    {
        abort_if(! in_array($loan->status, ['pending', 'under_review']), 422);

        $request->validate([
            'admin_notes' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $loan->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Application rejected.');
    }

    /**
     * Equipment profile compliance overview (separate tab / page).
     */
    public function profiles(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = TeacherEquipmentProfile::with('teacher')->latest('submitted_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $profiles = $query->paginate(30)->withQueryString();

        $counts = [
            'all'               => TeacherEquipmentProfile::count(),
            'meets_requirements'=> TeacherEquipmentProfile::where('status', 'meets_requirements')->count(),
            'needs_improvement' => TeacherEquipmentProfile::where('status', 'needs_improvement')->count(),
            'incomplete'        => TeacherEquipmentProfile::where('status', 'incomplete')->count(),
        ];

        return view('admin.equipment.profiles', compact('profiles', 'counts', 'status'));
    }
}
