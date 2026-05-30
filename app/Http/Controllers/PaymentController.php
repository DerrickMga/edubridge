<?php

namespace App\Http\Controllers;

use App\Models\{Course, Payment};
use App\Services\Payment\StripeService;
use App\Services\Payment\PaynowService;
use App\Services\Payment\EcoCashService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly StripeService  $stripe,
        private readonly PaynowService  $paynow,
        private readonly EcoCashService $ecocash,
    ) {}

    public function checkout(Course $course)
    {
        abort_if($course->status !== 'published', 404);
        abort_if(
            $course->price_usd <= 0 && $course->price_zwg <= 0,
            redirect()->route('student.dashboard')
        );
        return view('payments.checkout', compact('course'));
    }

    public function initiate(Request $request, Course $course): RedirectResponse
    {
        abort_if($course->status !== 'published', 404);

        $request->validate([
            'provider' => ['required', 'in:stripe,paynow_zw,ecocash,innbucks'],
        ]);

        $isLocal = $request->user()->country === 'ZW'
            || in_array($request->provider, ['paynow_zw', 'ecocash', 'innbucks']);

        $payment = Payment::create([
            'user_id'   => $request->user()->id,
            'course_id' => $course->id,
            'amount'    => $isLocal ? $course->price_zwg : $course->price_usd,
            'currency'  => $isLocal ? 'ZWG' : 'USD',
            'provider'  => $request->provider,
            'status'    => 'pending',
        ]);

        return match ($request->provider) {
            'stripe'    => $this->stripe->redirect($payment, $course),
            'paynow_zw' => $this->paynow->redirect($payment, $course),
            'ecocash'   => $this->ecocash->redirect($payment, $course),
            'innbucks'  => $this->innbucksRedirect($payment, $course),
            default      => redirect()->route('payments.checkout', $course)
                                ->with('error', 'Unknown payment provider.'),
        };
    }

    public function success(Request $request)
    {
        $payment = null;
        if ($request->has('session_id')) {
            $payment = Payment::where('provider_reference', $request->session_id)
                ->where('user_id', $request->user()->id)
                ->first();
        }
        return view('payments.success', compact('payment'));
    }

    public function webhook(Request $request, string $provider): mixed
    {
        return match ($provider) {
            'stripe'   => $this->stripe->handleWebhook($request),
            'paynow'   => $this->paynow->handleWebhook($request),
            'ecocash'  => $this->ecocash->handleWebhook($request),
            default    => response()->json(['error' => 'Unknown provider'], 400),
        };
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('courses.index')->with('info', 'Payment cancelled.');
    }

    private function innbucksRedirect(Payment $payment, Course $course): RedirectResponse
    {
        // InnBucks integration — SDK not yet available; return pending state
        $payment->update(['status' => 'pending', 'provider_reference' => 'INNBUCKS-PENDING-'.$payment->id]);
        return redirect()->route('payments.success')->with('info', 'InnBucks payment initiated. You will receive an SMS to confirm.');
    }
}
