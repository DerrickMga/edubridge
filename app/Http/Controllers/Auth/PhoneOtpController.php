<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PhoneOtpController extends Controller
{
    /** Show the phone number entry / OTP entry screen. */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->phone_verified_at) {
            return redirect()->route('onboarding', ['step' => 1]);
        }

        return view('auth.phone-otp');
    }

    /**
     * Generate a 6-digit OTP, store a bcrypt hash + expiry, and dispatch via WhatsApp.
     * Throttled to 3 attempts per minute by the route definition.
     */
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[1-9]\d{6,14}$/'],
        ]);

        $user  = $request->user();
        $phone = preg_replace('/[^\d+]/', '', $request->phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'phone'                => $phone,
            'phone_otp_code'       => Hash::make($code),
            'phone_otp_expires_at' => now()->addMinutes(10),
        ]);

        $sent = app(WhatsAppService::class)->sendOtp($phone, $code);

        if (! $sent) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Could not send OTP to that number. Please check it and try again.']);
        }

        return redirect()
            ->route('phone.verify')
            ->with('otp_sent', true)
            ->with('otp_phone', $phone);
    }

    /** Validate the submitted 6-digit code and mark the phone as verified. */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (! $user->phone_otp_expires_at || now()->isAfter($user->phone_otp_expires_at)) {
            return back()->withErrors(['code' => 'This OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($request->code, $user->phone_otp_code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $user->update([
            'phone_verified_at'    => now(),
            'phone_otp_code'       => null,
            'phone_otp_expires_at' => null,
        ]);

        return redirect()
            ->route('onboarding', ['step' => 1])
            ->with('success', 'Phone number verified!');
    }

    /** Allow the user to skip phone verification and proceed to onboarding. */
    public function skip(): RedirectResponse
    {
        return redirect()->route('onboarding', ['step' => 1]);
    }
}
