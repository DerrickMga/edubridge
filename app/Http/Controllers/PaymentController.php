<?php
namespace App\Http\Controllers;

use App\Models\{Course, Payment};
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function checkout(Course $course)
    {
        abort_if($course->status !== 'published', 404);
        return view('payments.checkout', compact('course'));
    }

    /**
     * Initiate a payment — stubs for Stripe (diaspora) & Paynow/EcoCash (local).
     */
    public function initiate(Request $request, Course $course)
    {
        $request->validate(['provider' => 'required|in:stripe,paynow_zw,ecocash,innbucks']);

        $payment = Payment::create([
            'user_id'   => $request->user()->id,
            'course_id' => $course->id,
            'amount'    => $request->user()->country === 'ZW' ? $course->price_zwg : $course->price_usd,
            'currency'  => $request->user()->country === 'ZW' ? 'ZWG' : 'USD',
            'provider'  => $request->provider,
            'status'    => 'pending',
        ]);

        return match($request->provider) {
            'stripe'     => $this->stripeRedirect($payment, $course),
            'paynow_zw'  => $this->paynowRedirect($payment, $course),
            'ecocash'    => $this->ecocashRedirect($payment, $course),
            'innbucks'   => $this->innbucksRedirect($payment, $course),
            default      => redirect()->route('payments.checkout', $course)->with('error', 'Unknown provider.'),
        };
    }

    public function success(Request $request)
    {
        return view('payments.success');
    }

    public function cancel()
    {
        return view('payments.cancel');
    }

    // ── Provider stubs ───────────────────────────────────────────────────────

    private function stripeRedirect(Payment $payment, Course $course)
    {
        // TODO: implement Stripe Checkout session creation
        // $session = \Stripe\Checkout\Session::create([...]);
        // return redirect($session->url);
        return redirect()->route('payments.success')->with('info', '[STUB] Stripe redirect goes here.');
    }

    private function paynowRedirect(Payment $payment, Course $course)
    {
        // TODO: implement Paynow Zimbabwe redirect
        return redirect()->route('payments.success')->with('info', '[STUB] Paynow ZW redirect goes here.');
    }

    private function ecocashRedirect(Payment $payment, Course $course)
    {
        // TODO: implement EcoCash push payment
        return redirect()->route('payments.success')->with('info', '[STUB] EcoCash push goes here.');
    }

    private function innbucksRedirect(Payment $payment, Course $course)
    {
        // TODO: implement InnBucks payment
        return redirect()->route('payments.success')->with('info', '[STUB] InnBucks redirect goes here.');
    }
}
