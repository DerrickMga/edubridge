<?php
namespace App\Services\Payment;

use App\Models\{Course, Payment};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function redirect(Payment $payment, Course $course): RedirectResponse
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
                        'product_data' => ['name' => $course->title],
                        'unit_amount'  => (int) ($payment->amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('payments.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('payments.cancel'),
                'metadata'    => ['payment_id' => $payment->id, 'course_id' => $course->id],
            ]);

            $payment->update(['provider_reference' => $session->id]);

            return redirect($session->url);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe checkout error', ['error' => $e->getMessage(), 'payment' => $payment->id]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('payments.checkout', $course)
                ->with('error', 'Payment failed. Please try again.');
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
                // Auto-enrol student
                $course = $payment->course;
                if ($course) {
                    $course->enrollments()->syncWithoutDetaching([$payment->user_id]);
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
