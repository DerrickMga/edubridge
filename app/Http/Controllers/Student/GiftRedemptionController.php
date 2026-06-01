<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;

class GiftRedemptionController extends Controller
{
    public function show(string $token)
    {
        $payment = Payment::where('gift_token', $token)->firstOrFail();
        return view('student.gifts.redeem', compact('payment'));
    }

    public function redeem(Request $request, string $token)
    {
        $payment = Payment::where('gift_token', $token)->firstOrFail();
        abort_if($payment->gift_redeemed_at, 422, 'Gift already redeemed.');
        abort_if($payment->status !== 'paid', 422, 'Gift payment not completed.');

        $user = $request->user();

        // Enrol the redeemer in the course
        Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $payment->course_id],
            ['enrolled_at' => now(), 'access_period' => $payment->access_period]
        );

        $payment->update([
            'gift_redeemed_at' => now(),
            'gift_redeemed_by' => $user->id,
        ]);

        return redirect()->route('courses.show', $payment->course_id)
            ->with('success', 'Gift redeemed. Welcome to the course!');
    }
}
