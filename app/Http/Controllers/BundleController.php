<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Payment;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function index()
    {
        $bundles = Bundle::where('is_active', true)->with('courses:id,title,thumbnail')->latest()->paginate(12);
        return view('bundles.index', compact('bundles'));
    }

    public function show(Bundle $bundle)
    {
        abort_unless($bundle->is_active, 404);
        $bundle->load('courses');
        return view('bundles.show', compact('bundle'));
    }

    public function buy(Request $request, Bundle $bundle)
    {
        abort_unless($bundle->is_active, 404);
        $user = $request->user();

        $payment = Payment::create([
            'user_id'   => $user->id,
            'course_id' => null,
            'bundle_id' => $bundle->id,
            'amount'    => $bundle->price_usd,
            'currency'  => 'USD',
            'provider'  => 'stripe',
            'status'    => 'pending',
        ]);

        return $this->stripe->checkout($payment, "Bundle: {$bundle->title}", ['bundle_id' => $bundle->id]);
    }
}
