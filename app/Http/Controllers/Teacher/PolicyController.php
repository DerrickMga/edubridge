<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherContract;
use App\Models\TeacherPolicy;
use App\Services\PolicyService;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function __construct(private readonly PolicyService $policies) {}

    /** Hub: outstanding ack list + current contract + library. */
    public function index(Request $request)
    {
        $teacher    = $request->user();
        $library    = TeacherPolicy::currentBySlug()->values();
        $outstanding = $this->policies->outstandingPoliciesFor($teacher);
        $contract   = TeacherContract::where('teacher_id', $teacher->id)
            ->whereIn('status', ['pending', 'signed'])
            ->latest('id')->first();

        return view('teacher.policies.index', compact('library', 'outstanding', 'contract'));
    }

    public function show(Request $request, TeacherPolicy $policy)
    {
        $acked = $request->user()->policyAcknowledgements()
            ->where('policy_id', $policy->id)
            ->where('version', $policy->version)
            ->exists();

        return view('teacher.policies.show', compact('policy', 'acked'));
    }

    public function acknowledge(Request $request, TeacherPolicy $policy)
    {
        $request->validate(['agree' => 'accepted']);
        $this->policies->acknowledge(
            $request->user(),
            $policy->id,
            $policy->version,
            $request->ip(),
            $request->userAgent(),
        );
        return back()->with('success', "Acknowledged: {$policy->title} (v{$policy->version}).");
    }

    /** Show or generate the teacher's contract. */
    public function contract(Request $request)
    {
        $teacher = $request->user();
        $contract = TeacherContract::where('teacher_id', $teacher->id)
            ->whereIn('status', ['pending', 'signed', 'superseded', 'terminated', 'expired'])
            ->latest('id')->first();

        // Auto-issue a pending contract if none exists yet (so teacher can self-serve).
        if (! $contract) {
            $contract = $this->policies->issueContract($teacher, []);
        }

        $outstanding = $this->policies->outstandingPoliciesFor($teacher);

        return view('teacher.policies.contract', compact('contract', 'outstanding'));
    }

    public function sign(Request $request, TeacherContract $contract)
    {
        abort_if($contract->teacher_id !== $request->user()->id, 403);
        abort_unless($contract->status === 'pending', 422, 'Contract is not pending.');

        $data = $request->validate([
            'typed_name' => 'required|string|max:200',
            'agree'      => 'accepted',
        ]);

        // Block signing if any current policy is unacked.
        $outstanding = $this->policies->outstandingPoliciesFor($request->user());
        if ($outstanding->isNotEmpty()) {
            return back()->withErrors(['agree' => 'Please acknowledge every active policy before signing the contract.']);
        }

        $this->policies->signContract($contract, $data['typed_name'], $request->ip(), $request->userAgent());

        return redirect()->route('teacher.policies.contract')
            ->with('success', 'Contract signed. A signed copy has been recorded against your account.');
    }
}
