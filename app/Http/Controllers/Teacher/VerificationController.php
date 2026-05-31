<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $verification = $request->user()->verification;
        return view('teacher.verification.index', compact('verification'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only allow submit/resubmit if no approved verification exists
        if ($user->verification && $user->verification->isApproved()) {
            return back()->with('error', 'Your account is already verified.');
        }

        $data = $request->validate([
            'full_legal_name'      => ['required', 'string', 'max:255'],
            'national_id_number'   => ['required', 'string', 'max:50'],
            'id_document_front'    => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_document_back'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'proof_of_qualification' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie_with_id'       => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'bank_name'            => ['nullable', 'string', 'max:100'],
            'bank_account_number'  => ['nullable', 'string', 'max:50'],
            'bank_branch_code'     => ['nullable', 'string', 'max:20'],
            'ecocash_number'       => ['nullable', 'string', 'max:20'],
            'innbucks_number'      => ['nullable', 'string', 'max:20'],
            'paynow_email'         => ['nullable', 'email', 'max:255'],
        ]);

        // Store uploaded files
        $storedFiles = [];
        foreach (['id_document_front', 'id_document_back', 'proof_of_qualification', 'selfie_with_id'] as $field) {
            if ($request->hasFile($field)) {
                $storedFiles[$field] = $request->file($field)->store("verifications/{$user->id}", 'private');
            }
        }

        $payload = array_merge(
            array_diff_key($data, array_flip(['id_document_front', 'id_document_back', 'proof_of_qualification', 'selfie_with_id'])),
            $storedFiles,
            ['teacher_id' => $user->id, 'status' => 'pending', 'submitted_at' => now(), 'admin_notes' => null, 'reviewed_by' => null, 'reviewed_at' => null]
        );

        if ($user->verification) {
            $user->verification->update($payload);
        } else {
            TeacherVerification::create($payload);
        }

        return redirect()->route('teacher.verification.index')
            ->with('success', 'Verification documents submitted. We\'ll review within 1–2 business days.');
    }
}
