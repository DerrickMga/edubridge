<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;

class SubscriptionPurchaseController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function buy(Request $request, SubscriptionPlan $plan)
    {
        abort_unless($plan->is_active, 404);
        $user = $request->user();

        // Create (or reuse) a Subscription row in pending state
        $sub = Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'status'     => 'canceled', // becomes 'active' on webhook
            'started_at' => now(),
            'expires_at' => now(), // overwritten on webhook
        ]);

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $sub->id,
            'amount'          => $plan->price_usd,
            'currency'        => 'USD',
            'provider'        => 'stripe',
            'status'          => 'pending',
        ]);

        $sub->update(['payment_id' => $payment->id]);

        return $this->stripe->checkout($payment, "Subscription: {$plan->name} ({$plan->interval})", [
            'subscription_id' => $sub->id,
            'plan_id'         => $plan->id,
        ]);
    }
}
