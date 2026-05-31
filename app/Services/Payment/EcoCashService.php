<?php
namespace App\Services\Payment;

use App\Models\{Course, Payment, User};
use App\Notifications\EnrollmentConfirmationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EcoCash Mobile Money (Zimbabwe) integration.
 * Uses server-to-server USSD push to subscriber's phone.
 */
class EcoCashService
{
    public function redirect(Payment $payment, Course $course): RedirectResponse
    {
        $merchantCode = config('services.ecocash.merchant_code', '');
        $merchantPin  = config('services.ecocash.merchant_pin', '');
        $baseUrl      = config('services.ecocash.base_url', '');

        if (empty($merchantCode) || empty($merchantPin)) {
            return redirect()->route('payments.success')
                ->with('info', 'EcoCash integration coming soon. A support agent will contact you.');
        }

        $phone = $payment->user->phone ?? null;
        if (!$phone) {
            return redirect()->route('payments.checkout', $course)
                ->with('error', 'Please add your EcoCash phone number to your profile first.');
        }

        try {
            $response = Http::withBasicAuth($merchantCode, $merchantPin)
                ->post($baseUrl.'/transactions/initiate', [
                    'clientCorrelator'    => 'EB-'.$payment->id,
                    'notifyUrl'           => route('payments.webhook', 'ecocash'),
                    'referenceCode'       => 'EduBridge-Course-'.$course->id,
                    'tranType'            => 'MER',
                    'endUserId'           => $phone,
                    'amount'              => ['charginginformation' => ['amount' => $payment->amount, 'currency' => 'ZWG', 'description' => $course->title]],
                    'merchantCode'        => $merchantCode,
                    'merchantPin'         => $merchantPin,
                    'merchantNumber'      => $merchantCode,
                    'currencyCode'        => 'ZWG',
                    'countryCode'         => 'ZW',
                    'terminalID'          => 'WEB001',
                    'location'            => 'ONLINE',
                    'superMerchantName'   => 'EduBridge',
                    'merchantName'        => 'EduBridge Learning',
                ]);

            $payment->update(['provider_reference' => 'ECOCASH-'.$payment->id]);

            if ($response->successful()) {
                return redirect()->route('payments.success')
                    ->with('info', 'EcoCash push sent to '.$phone.'. Approve on your phone to complete.');
            }

            throw new \RuntimeException('EcoCash API returned: '.$response->status());
        } catch (\Throwable $e) {
            Log::error('EcoCash payment error', ['error' => $e->getMessage(), 'payment' => $payment->id]);
            $payment->update(['status' => 'failed']);
            return redirect()->route('payments.checkout', $course)
                ->with('error', 'EcoCash push failed. Please try again.');
        }
    }

    public function handleWebhook(Request $request): mixed
    {
        $data       = $request->json()->all();
        $reference  = $data['clientCorrelator'] ?? null;
        $statusCode = $data['transactionOperationStatus'] ?? null;

        if ($reference) {
            $paymentId = str_replace('EB-', '', $reference);
            $payment   = Payment::find($paymentId);
            if ($payment && strtolower($statusCode ?? '') === 'completed') {
                $payment->update(['status' => 'paid']);
                $course = $payment->course;
                if ($course) {
                    $course->enrollments()->syncWithoutDetaching([$payment->user_id]);
                    $student = User::find($payment->user_id);
                    if ($student) {
                        $student->notify(new EnrollmentConfirmationNotification($course, $payment));
                    }
                }
            }
        }

        return response()->json(['status' => 'received']);
    }
}
