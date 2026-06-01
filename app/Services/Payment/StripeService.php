<?php
namespace App\Services\Payment;

use App\Models\{Course, Payment, User};
use App\Notifications\EnrollmentConfirmationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function redirect(Payment $payment, Course $course): RedirectResponse
    {
        return $this->checkout($payment, $course->title, ['payment_id' => $payment->id, 'course_id' => $course->id]);
    }

    /**
     * Stripe Checkout for a Payment of any kind (course, subscription, gift, bundle).
     * Pass a friendly product title and optional metadata.
     */
    public function checkout(Payment $payment, string $title, array $metadata = []): RedirectResponse
    {
        $stripeSecret = config('services.stripe.secret');

        if (empty($stripeSecret)) {
            Log::warning('Stripe secret not configured; returning stub response.');
            return redirect()->route('payments.success')
                ->with('info', 'Stripe is not yet configured. Please contact support.');
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => strtolower($payment->currency),
                        'product_data' => ['name' => $title],
                        'unit_amount'  => (int) ($payment->amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('payments.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('payments.cancel'),
                'metadata'    => array_merge(['payment_id' => $payment->id], $metadata),
            ]);

            $payment->update(['provider_reference' => $session->id]);

            return redirect($session->url);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe checkout error', ['error' => $e->getMessage(), 'payment' => $payment->id]);
            $payment->update(['status' => 'failed']);
            return redirect()->back()->with('error', 'Payment failed. Please try again.');
        }
    }

    public function handleWebhook(Request $request): mixed
    {
        $webhookSecret = config('services.stripe.webhook_secret');
        $payload       = $request->getContent();
        $sigHeader     = $request->header('Stripe-Signature');

        if (empty($webhookSecret)) {
            return response()->json(['error' => 'Webhook not configured'], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $payment = Payment::where('provider_reference', $session->id)->first();
            if ($payment) {
                $payment->update(['status' => 'paid']);

                // Subscription: activate / extend
                if ($payment->subscription_id) {
                    $sub = \App\Models\Subscription::find($payment->subscription_id);
                    if ($sub) {
                        $months = $sub->plan?->interval === 'year' ? 12 : 1;
                        $sub->update([
                            'status'     => 'active',
                            'started_at' => $sub->started_at ?? now(),
                            'expires_at' => ($sub->expires_at && $sub->expires_at->isFuture() ? $sub->expires_at : now())->copy()->addMonths($months),
                        ]);
                    }
                }

                // Bundle: enrol in all member courses
                if ($payment->bundle_id) {
                    $bundle = \App\Models\Bundle::with('courses')->find($payment->bundle_id);
                    if ($bundle) {
                        foreach ($bundle->courses as $c) {
                            $c->enrollments()->syncWithoutDetaching([$payment->user_id]);
                        }
                    }
                }

                // Course (standard or gift)
                if ($payment->course_id && ! $payment->gift_token) {
                    $course = $payment->course;
                    if ($course) {
                        $course->enrollments()->syncWithoutDetaching([$payment->user_id]);
                        $student = User::find($payment->user_id);
                        if ($student) {
                            $student->notify(new EnrollmentConfirmationNotification($course, $payment));
                        }
                    }
                }

                // Referral credit (10% of paid amount on the buyer's first paid order)
                $buyer = User::find($payment->user_id);
                if ($buyer && $buyer->referred_by) {
                    $alreadyCredited = \App\Models\ReferralCredit::where('referred_id', $buyer->id)->exists();
                    if (! $alreadyCredited) {
                        \App\Models\ReferralCredit::create([
                            'referrer_id' => $buyer->referred_by,
                            'referred_id' => $buyer->id,
                            'payment_id'  => $payment->id,
                            'amount'      => round((float) $payment->amount * 0.10, 2),
                            'currency'    => $payment->currency,
                            'status'      => 'credited',
                        ]);
                    }
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
