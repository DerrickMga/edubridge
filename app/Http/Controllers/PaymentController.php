<?php

namespace App\Http\Controllers;

use App\Models\{Course, Payment};
use App\Models\Setting;
use App\Services\Payment\StripeService;
use App\Services\Payment\PaynowService;
use App\Services\Payment\EcoCashService;
use App\Services\Payment\PayFastService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    /** Period labels shown in UI. */
    public const PERIOD_LABELS = [
        'hourly'  => '1 Hour',
        'monthly' => '1 Month',
        'termly'  => '1 Term (3 months)',
    ];

    /** Load live prices from settings table. */
    private static function periodPrices(): array
    {
        return [
            'hourly'  => (float) Setting::get('price_hourly',  1.00),
            'monthly' => (float) Setting::get('price_monthly', 5.00),
            'termly'  => (float) Setting::get('price_termly',  10.00),
        ];
    }

    /** Load live ZWG rate from settings table. */
    private static function zwgRate(): float
    {
        return (float) Setting::get('price_zwg_rate', 30);
    }

    public function __construct(
        private readonly StripeService   $stripe,
        private readonly PaynowService   $paynow,
        private readonly EcoCashService  $ecocash,
        private readonly PayFastService  $payfast,
    ) {}

    public function checkout(Course $course)
    {
        abort_if($course->status !== 'published', 404);

        // Already enrolled? Redirect to course
        if (auth()->check() && $course->enrollments()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('student.dashboard')
                ->with('info', 'You are already enrolled in this course.');
        }

        // TODO: Enrollment is temporarily free — skip payment for all courses
        $course->enrollments()->syncWithoutDetaching([auth()->id() => [
            'status'        => 'active',
            'access_period' => 'termly',
            'expires_at'    => now()->addMonths(3),
        ]]);
        return redirect()->route('student.dashboard')
            ->with('success', 'You have been enrolled in ' . $course->title . '!');

        // Build period pricing for checkout view (reached when above TODO is removed)
        $zwgRate      = self::zwgRate();
        $periodPricing = collect(self::periodPrices())->map(fn($usd, $key) => [
            'key'   => $key,
            'label' => self::PERIOD_LABELS[$key],
            'usd'   => $usd,
            'zwg'   => round($usd * $zwgRate, 0),
        ]);

        return view('payments.checkout', compact('course', 'periodPricing'));
    }

    public function initiate(Request $request, Course $course): mixed
    {
        abort_if($course->status !== 'published', 404);

        $request->validate([
            'provider'      => ['required', 'in:stripe,paynow_zw,ecocash,innbucks,payfast'],
            'access_period' => ['required', 'in:hourly,monthly,termly'],
        ]);

        $isLocal   = $request->user()->country === 'ZW'
            || in_array($request->provider, ['paynow_zw', 'ecocash', 'innbucks']);

        $usdAmount = self::periodPrices()[$request->access_period];
        $amount    = $isLocal ? round($usdAmount * self::zwgRate(), 0) : $usdAmount;

        // PayFast charges in ZAR — store USD, service converts on redirect
        $currency = match ($request->provider) {
            'paynow_zw', 'ecocash', 'innbucks' => 'ZWG',
            'payfast'                           => 'USD',  // converted to ZAR in service
            default                             => 'USD',
        };

        $payment = Payment::create([
            'user_id'       => $request->user()->id,
            'course_id'     => $course->id,
            'amount'        => $amount,
            'currency'      => $currency,
            'provider'      => $request->provider,
            'access_period' => $request->access_period,
            'status'        => 'pending',
        ]);

        return match ($request->provider) {
            'stripe'    => $this->stripe->redirect($payment, $course),
            'paynow_zw' => $this->paynow->redirect($payment, $course),
            'ecocash'   => $this->ecocash->redirect($payment, $course),
            'payfast'   => $this->payfast->redirect($payment, $course),
            'innbucks'  => $this->innbucksRedirect($payment, $course),
            default     => redirect()->route('payments.checkout', $course)
                               ->with('error', 'Unknown payment provider.'),
        };
    }

    public function success(Request $request)
    {
        $payment = null;

        if ($request->has('session_id')) {
            // Stripe
            $payment = Payment::where('provider_reference', $request->session_id)
                ->where('user_id', $request->user()->id)
                ->first();
        } elseif ($request->has('pf_payment_id')) {
            // PayFast return URL passes our internal payment id
            $payment = Payment::where('id', $request->pf_payment_id)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('payments.success', compact('payment'));
    }

    public function webhook(Request $request, string $provider): mixed
    {
        return match ($provider) {
            'stripe'  => $this->stripe->handleWebhook($request),
            'paynow'  => $this->paynow->handleWebhook($request),
            'ecocash' => $this->ecocash->handleWebhook($request),
            'payfast' => $this->payfast->handleWebhook($request),
            default   => response()->json(['error' => 'Unknown provider'], 400),
        };
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('courses.index')->with('info', 'Payment cancelled.');
    }

    private function innbucksRedirect(Payment $payment, Course $course): RedirectResponse
    {
        $payment->update(['status' => 'pending', 'provider_reference' => 'INNBUCKS-PENDING-'.$payment->id]);
        return redirect()->route('payments.success')
            ->with('info', 'InnBucks payment initiated. You will receive an SMS to confirm.');
    }
}

