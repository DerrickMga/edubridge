<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price_usd')->get();
        $active = $request->user()->activeSubscription();
        $history = $request->user()->subscriptions()->with('plan', 'payment')->latest()->take(20)->get();

        return view('student.subscriptions.index', compact('plans', 'active', 'history'));
    }

    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        abort_unless($plan->is_active, 404);

        // Route to checkout with a marker so PaymentController knows this is a subscription
        return redirect()->route('payments.checkout', [
            'plan_id' => $plan->id,
        ])->with('checkout_kind', 'subscription');
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);
        $subscription->update(['status' => 'canceled', 'canceled_at' => now()]);
        return back()->with('success', 'Subscription canceled. Access continues until '.$subscription->expires_at->format('d M Y').'.');
    }
}
