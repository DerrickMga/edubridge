<?php
namespace App\Services\Payment;

use App\Models\{Course, Payment};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Paynow Zimbabwe integration.
 * Docs: https://developers.paynow.co.zw/docs
 */
class PaynowService
{
    private string $integrationId;
    private string $integrationKey;
    private string $resultUrl;
    private string $returnUrl;

    public function __construct()
    {
        $this->integrationId  = config('services.paynow.integration_id', '');
        $this->integrationKey = config('services.paynow.integration_key', '');
        $this->resultUrl      = config('services.paynow.result_url', route('payments.webhook', 'paynow'));
        $this->returnUrl      = config('services.paynow.return_url', route('payments.success'));
    }

    public function redirect(Payment $payment, Course $course): RedirectResponse
    {
        if (empty($this->integrationId) || empty($this->integrationKey)) {
            return redirect()->route('payments.success')
                ->with('info', 'Paynow is not yet configured. Contact support to pay via EcoCash/Paynow.');
        }

        $fields = [
            'id'             => $this->integrationId,
            'reference'      => 'EB-'.$payment->id,
            'amount'         => number_format($payment->amount, 2, '.', ''),
            'additionalinfo' => $course->title,
            'returnurl'      => $this->returnUrl,
            'resulturl'      => $this->resultUrl,
            'status'         => 'Message',
        ];
        $fields['hash'] = $this->hash($fields);

        $response = Http::asForm()->post('https://www.paynow.co.zw/interface/initiatetransaction', $fields);
        $result   = $this->parseResponse($response->body());

        if (isset($result['status']) && strtolower($result['status']) === 'ok') {
            $payment->update(['provider_reference' => $result['pollurl'] ?? null]);
            return redirect($result['browserurl']);
        }

        Log::error('Paynow initiation failed', ['response' => $result, 'payment' => $payment->id]);
        $payment->update(['status' => 'failed']);
        return redirect()->route('payments.checkout', $course)
            ->with('error', 'Paynow payment failed. Please try again.');
    }

    public function handleWebhook(Request $request): mixed
    {
        $data = $request->all();
        if (!isset($data['hash']) || $this->hash($data) !== $data['hash']) {
            return response()->json(['error' => 'Invalid hash'], 400);
        }

        $payment = Payment::where('provider_reference', $data['pollurl'] ?? '')->first();
        if ($payment && strtolower($data['status'] ?? '') === 'paid') {
            $payment->update(['status' => 'paid']);
            $payment->course?->enrollments()->syncWithoutDetaching([$payment->user_id]);
        }

        return response('OK');
    }

    private function hash(array $fields): string
    {
        $values = implode('', array_values($fields));
        return strtoupper(hash_hmac('sha512', $values, $this->integrationKey));
    }

    private function parseResponse(string $body): array
    {
        $result = [];
        foreach (explode('&', $body) as $pair) {
            [$k, $v] = array_pad(explode('=', $pair, 2), 2, '');
            $result[urldecode($k)] = urldecode($v);
        }
        return $result;
    }
}
