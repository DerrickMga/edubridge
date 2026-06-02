<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard (step depends on URL param, defaults to 1).
     */
    public function show(Request $request, int $step = 1): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        $maxStep = $user->role === 'teacher' ? 4 : 4;

        if ($step < 1 || $step > $maxStep) {
            return redirect()->route('onboarding', ['step' => 1]);
        }

        return view('onboarding.wizard', [
            'user'    => $user,
            'step'    => $step,
            'maxStep' => $maxStep,
        ]);
    }

    /**
     * Save profile data from a step and advance.
     */
    public function save(Request $request, int $step): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasCompletedOnboarding()) {
            return redirect()->route('dashboard');
        }

        match (true) {
            $user->role === 'student' && $step === 2 => $this->saveStudentProfile($request, $user),
            $user->role === 'teacher' && $step === 2 => $this->saveTeacherProfile($request, $user),
            default => null,
        };

        $maxStep = 4;
        $next    = $step + 1;

        if ($next > $maxStep) {
            return $this->complete($request);
        }

        return redirect()->route('onboarding', ['step' => $next]);
    }

    /**
     * Mark onboarding complete and redirect to dashboard.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['onboarding_completed_at' => now()]);

        return redirect()->route('dashboard')->with('onboarding_done', true);
    }

    /* ─────────────────── private helpers ──────────────────── */

    private function saveStudentProfile(Request $request, $user): void
    {
        $validated = $request->validate([
            'grade_level' => ['nullable', 'string', 'max:50'],
            'country'     => ['nullable', 'string', 'max:100'],
            'city'        => ['nullable', 'string', 'max:100'],
            'timezone'    => ['nullable', 'string', 'max:100'],
            'bio'         => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update(array_filter($validated, fn($v) => $v !== null));
    }

    private function saveTeacherProfile(Request $request, $user): void
    {
        $validated = $request->validate([
            'qualification'  => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string', 'max:2000'],
            'timezone'       => ['nullable', 'string', 'max:100'],
            'country'        => ['nullable', 'string', 'max:100'],
            'hourly_rate_usd'=> ['nullable', 'numeric', 'min:0', 'max:9999'],
        ]);

        $user->update(array_filter($validated, fn($v) => $v !== null));
    }
}
