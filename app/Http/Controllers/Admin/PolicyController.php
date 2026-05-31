<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyRiskEvent;
use App\Models\PolicyRiskIncident;
use App\Models\TeacherContract;
use App\Models\TeacherPolicy;
use App\Models\User;
use App\Services\PolicyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function __construct(private readonly PolicyService $policies) {}

    /** Library + contract roster + incident counts. */
    public function index()
    {
        $library = TeacherPolicy::orderBy('category')->orderBy('slug')->orderByDesc('version')->get();

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get();

        $contracts = TeacherContract::with('teacher')
            ->whereIn('status', ['pending', 'signed', 'expired'])
            ->latest('id')->limit(200)->get();

        $kpis = [
            'policies'   => $library->where('is_active', true)->count(),
            'teachers'   => $teachers->count(),
            'signed'     => $teachers->filter(fn ($t) => $t->hasSignedContract())->count(),
            'pending'    => TeacherContract::where('status', 'pending')->count(),
            'open_inc'   => PolicyRiskIncident::whereIn('status', ['open', 'investigating'])->count(),
        ];

        return view('admin.policies.index', compact('library', 'contracts', 'teachers', 'kpis'));
    }

    public function createPolicy()
    {
        return view('admin.policies.editor', ['policy' => null]);
    }

    public function storePolicy(Request $request)
    {
        $data = $request->validate([
            'slug'      => 'required|string|max:80|regex:/^[a-z0-9-]+$/',
            'title'     => 'required|string|max:200',
            'category'  => 'required|string|max:40',
            'body'      => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        $latest = TeacherPolicy::where('slug', $data['slug'])->max('version') ?? 0;
        TeacherPolicy::create($data + [
            'version'      => $latest + 1,
            'effective_at' => now(),
            'created_by'   => $request->user()->id,
            'is_active'    => (bool) ($data['is_active'] ?? true),
        ]);
        return redirect()->route('admin.policies.index')->with('success', 'Policy version created.');
    }

    public function togglePolicy(TeacherPolicy $policy)
    {
        $policy->is_active = ! $policy->is_active;
        $policy->save();
        return back()->with('success', 'Policy '.($policy->is_active ? 'activated' : 'deactivated').'.');
    }

    /** Issue or open a teacher contract for review. */
    public function showContract(User $teacher)
    {
        $contract = TeacherContract::where('teacher_id', $teacher->id)
            ->latest('id')->first();
        $outstanding = $this->policies->outstandingPoliciesFor($teacher);
        $incidents = PolicyRiskIncident::where('teacher_id', $teacher->id)
            ->with('event')->latest('id')->limit(50)->get();

        return view('admin.policies.contract', compact('teacher', 'contract', 'outstanding', 'incidents'));
    }

    public function issueContract(Request $request, User $teacher)
    {
        $data = $request->validate([
            'rate_usd'       => 'required|numeric|min:0.01',
            'payment_terms'  => 'nullable|string|max:80',
            'term_months'    => 'nullable|integer|min:1|max:60',
            'exclusivity'    => ['nullable', Rule::in(['exclusive', 'non_exclusive'])],
            'addendum'       => 'nullable|string',
        ]);
        $this->policies->issueContract($teacher, $data, $request->user()->id);
        return back()->with('success', 'New contract issued for this teacher.');
    }

    public function terminateContract(Request $request, TeacherContract $contract)
    {
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $contract->update([
            'status'            => 'terminated',
            'terminated_at'     => now(),
            'terminated_reason' => $data['reason'],
        ]);
        return back()->with('success', 'Contract terminated.');
    }

    /* ---------------- Risk / contingency matrix ---------------- */

    public function matrix()
    {
        $events = PolicyRiskEvent::with('policy')
            ->orderBy('severity')->orderBy('category')->orderBy('title')->get();
        $bySeverity = $events->groupBy('severity');
        $incidents = PolicyRiskIncident::with('event', 'teacher')
            ->orderByDesc('opened_at')->limit(100)->get();

        return view('admin.policies.matrix', compact('events', 'bySeverity', 'incidents'));
    }

    public function storeMatrixEvent(Request $request)
    {
        $data = $request->validate([
            'code'              => 'required|string|max:40|regex:/^[A-Z0-9_]+$/|unique:policy_risk_events,code',
            'title'             => 'required|string|max:200',
            'category'          => ['required', Rule::in(PolicyRiskEvent::categories())],
            'severity'          => ['required', Rule::in(PolicyRiskEvent::severities())],
            'trigger'           => 'nullable|string',
            'response_playbook' => 'required|string',
            'policy_id'         => 'nullable|exists:teacher_policies,id',
        ]);
        PolicyRiskEvent::create($data + ['is_active' => true]);
        return back()->with('success', 'Risk event added to matrix.');
    }

    public function logIncident(Request $request)
    {
        $data = $request->validate([
            'risk_event_id'     => 'required|exists:policy_risk_events,id',
            'teacher_id'        => 'required|exists:users,id',
            'summary'           => 'required|string|max:2000',
            'severity_override' => ['nullable', Rule::in(PolicyRiskEvent::severities())],
        ]);
        PolicyRiskIncident::create($data + [
            'reported_by' => $request->user()->id,
            'status'      => 'open',
            'opened_at'   => now(),
        ]);
        return back()->with('success', 'Incident logged.');
    }

    public function updateIncident(Request $request, PolicyRiskIncident $incident)
    {
        $data = $request->validate([
            'status'           => ['required', Rule::in(PolicyRiskIncident::statuses())],
            'resolution_notes' => 'nullable|string',
        ]);
        $incident->fill($data);
        if (in_array($data['status'], ['resolved', 'dismissed'], true) && ! $incident->closed_at) {
            $incident->closed_at = now();
        }
        $incident->save();
        return back()->with('success', 'Incident updated.');
    }
}
