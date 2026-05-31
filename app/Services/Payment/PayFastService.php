<?php
namespace App\Services\Payment;

use App\Models\{Course, Payment, User};
use App\Notifications\EnrollmentConfirmationNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * PayFastService — PayFast payment gateway integration for EduBridge.
 *
 * Ported from the KMG VitalBot PayFast controller:
 *   app/Http/Controllers/Payments/PayFast/PayfastController.php
 *
 * Flow:
 *   1. redirect()  — builds signed form data, returns auto-submit view to PayFast
 *   2. handleWebhook() — verifies ITN signature, marks payment paid, auto-enrols
 */
class PayFastService
{
    /** Period in months (null = no expiry / lifetime). */
    private const PERIOD_MONTHS = [
        'monthly' => 1,
        'termly'  => 3,
        'annual'  => 12,
        'lifetime'=> null,
    ];

    // ── Redirect ─────────────────────────────────────────────────────────────

    /**
     * Build the PayFast redirect payload, sign it, and return the auto-submit view.
     */
    public function redirect(Payment $payment, Course $course): RedirectResponse|\Illuminate\View\View
    {
        $merchantId  = config('services.payfast.merchant_id');
        $merchantKey = config('services.payfast.merchant_key');

        if (empty($merchantId) || empty($merchantKey)) {
            Log::warning('PayFastService: Merchant ID or Key not configured.', ['payment' => $payment->id]);
            return redirect()->route('payments.checkout', $course)
                ->with('error', 'PayFast is not yet configured. Please use another payment method.');
        }

        $user    = User::find($payment->user_id);
        $amount  = $this->zarAmount($payment);

        $data = [
            'merchant_id'  => $merchantId,
            'merchant_key' => $merchantKey,
            'return_url'   => route('payments.success') . '?pf_payment_id=' . $payment->id,
            'cancel_url'   => route('payments.cancel'),
            'notify_url'   => route('payments.webhook', 'payfast'),
            // Buyer
            'name_first'   => $user ? explode(' ', $user->name)[0] : 'Student',
            'name_last'    => $user ? (explode(' ', $user->name)[1] ?? '') : '',
            'email_address'=> $user?->email ?? '',
            // Transaction
            'm_payment_id' => (string) $payment->id,
            'amount'       => number_format($amount, 2, '.', ''),
            'item_name'    => 'EduBridge — ' . $course->title,
            'item_description' => ucfirst($payment->access_period ?? 'lifetime') . ' access',
        ];

        // Remove blank fields before signing (PayFast excludes empty values)
        $data = array_filter($data, fn($v) => $v !== '' && $v !== null);

        $data['signature'] = $this->buildSignature($data);

        $url = config('services.payfast.test_mode')
            ? 'https://sandbox.payfast.co.za/eng/process'
            : 'https://www.payfast.co.za/eng/process';

        Log::info('PayFastService: Redirecting to PayFast', [
            'payment_id' => $payment->id,
            'amount_zar' => $amount,
            'item'       => $data['item_name'],
            'test_mode'  => config('services.payfast.test_mode', false),
        ]);

        return view('payments.payfast-redirect', compact('data', 'url'));
    }

    // ── ITN Webhook ───────────────────────────────────────────────────────────

    /**
     * Handle PayFast Instant Transaction Notification (ITN).
     * Verifies signature, marks payment paid, auto-enrols the student.
     */
    public function handleWebhook(Request $request): Response
    {
        // 1 — Signature verification (ported from VitalBot)
        if (!$this->verifyItnSignature($request)) {
            Log::warning('PayFastService: ITN invalid signature', [
                'm_payment_id' => $request->m_payment_id,
                'ip'           => $request->ip(),
            ]);
            return response('Invalid signature', 403);
        }

        $payment = Payment::find($request->m_payment_id);

        if (!$payment) {
            Log::error('PayFastService: Payment not found for ITN', ['m_payment_id' => $request->m_payment_id]);
            return response('OK', 200); // Acknowledge to stop retries
        }

        // Idempotent — skip if already handled
        if ($payment->status === 'paid') {
            return response('OK', 200);
        }

        if ($request->payment_status === 'COMPLETE') {
            $payment->update([
                'status'             => 'paid',
                'provider_reference' => $request->pf_payment_id,
            ]);

            Log::info('PayFastService: Payment confirmed', [
                'payment_id'    => $payment->id,
                'pf_payment_id' => $request->pf_payment_id,
                'amount'        => $request->amount_gross,
                'access_period' => $payment->access_period,
            ]);

            $this->enrolStudent($payment);
        } elseif (in_array($request->payment_status, ['FAILED', 'CANCELLED'])) {
            $payment->update(['status' => 'failed']);
            Log::info('PayFastService: Payment failed/cancelled', [
                'payment_id' => $payment->id,
                'pf_status'  => $request->payment_status,
            ]);
        }

        return response('OK', 200);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Auto-enrol the student with the correct period expiry.
     */
    private function enrolStudent(Payment $payment): void
    {
        $course = $payment->course;
        if (!$course) {
            return;
        }

        $period    = $payment->access_period ?? 'lifetime';
        $months    = self::PERIOD_MONTHS[$period] ?? null;
        $expiresAt = $months ? Carbon::now()->addMonths($months) : null;

        // Upsert enrollment pivot with period data
        $course->enrollments()->syncWithoutDetaching([
            $payment->user_id => [
                'status'        => 'active',
                'access_period' => $period,
                'expires_at'    => $expiresAt,
            ],
        ]);

        $student = User::find($payment->user_id);
        if ($student) {
            try {
                $student->notify(new EnrollmentConfirmationNotification($course));
            } catch (\Throwable $e) {
                Log::warning('PayFastService: Could not send enrollment notification', [
                    'user_id'    => $student->id,
                    'payment_id' => $payment->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        Log::info('PayFastService: Student enrolled via PayFast', [
            'user_id'    => $payment->user_id,
            'course_id'  => $course->id,
            'period'     => $period,
            'expires_at' => $expiresAt?->toDateTimeString() ?? 'never',
        ]);
    }

    /**
     * Convert payment amount to ZAR for PayFast.
     * PayFast only accepts ZAR — USD is converted using a fixed rate.
     */
    private function zarAmount(Payment $payment): float
    {
        if ($payment->currency === 'ZAR') {
            return (float) $payment->amount;
        }
        // USD → ZAR (use config rate; fallback 18.5)
        $usdRate = (float) config('services.payfast.usd_zar_rate', 18.5);
        return round((float) $payment->amount * $usdRate, 2);
    }

    /**
     * Build the PayFast MD5 signature.
     * Matches VitalBot implementation exactly.
     */
    private function buildSignature(array $data): string
    {
        $query      = http_build_query($data);
        $passphrase = config('services.payfast.passphrase', '');
        if ($passphrase) {
            $query .= '&passphrase=' . urlencode($passphrase);
        }
        return md5($query);
    }

    /**
     * Verify the PayFast ITN signature (ported from VitalBot).
     */
    private function verifyItnSignature(Request $request): bool
    {
        $pfData        = $request->except('signature');
        $pfParamString = '';

        foreach ($pfData as $key => $val) {
            if ($val !== '') {
                $pfParamString .= $key . '=' . urlencode(trim((string) $val)) . '&';
            }
        }

        $pfParamString = rtrim($pfParamString, '&');
        $passphrase    = config('services.payfast.passphrase', '');

        if ($passphrase) {
            $pfParamString .= '&passphrase=' . urlencode($passphrase);
        }

        $calculated = md5($pfParamString);
        $received   = $request->input('signature', '');

        return hash_equals($calculated, $received);
    }
}
