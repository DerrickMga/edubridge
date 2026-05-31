<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $verifications = TeacherVerification::with('teacher')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest('submitted_at')
            ->paginate(25);

        $counts = [
            'all'                => TeacherVerification::count(),
            'pending'            => TeacherVerification::where('status', 'pending')->count(),
            'approved'           => TeacherVerification::where('status', 'approved')->count(),
            'rejected'           => TeacherVerification::where('status', 'rejected')->count(),
            'needs_resubmission' => TeacherVerification::where('status', 'needs_resubmission')->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'status', 'counts'));
    }

    public function show(TeacherVerification $verification)
    {
        $verification->load('teacher', 'reviewer');
        return view('admin.verifications.show', compact('verification'));
    }

    public function approve(Request $request, TeacherVerification $verification): RedirectResponse
    {
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $verification->update([
            'status'      => 'approved',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Teacher {$verification->teacher->name} has been verified.");
    }

    public function reject(Request $request, TeacherVerification $verification): RedirectResponse
    {
        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
            'status'      => ['required', 'in:rejected,needs_resubmission'],
        ]);

        $verification->update([
            'status'      => $data['status'],
            'admin_notes' => $data['admin_notes'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $action = $data['status'] === 'rejected' ? 'rejected' : 'sent back for resubmission';
        return back()->with('success', "Verification {$action}.");
    }

    public function downloadDocument(TeacherVerification $verification, string $field): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $allowed = ['id_document_front', 'id_document_back', 'proof_of_qualification', 'selfie_with_id'];
        abort_if(! in_array($field, $allowed), 404);

        $path = $verification->{$field};
        abort_if(! $path || ! Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path);
    }
}
