<?php

namespace App\Payment\Processors\Paypal;

use App\Payment\Exceptions\InitiatePaymentException;
use App\Payment\Exceptions\PaypalProcessorException;
use App\Payment\Processors\PaymentInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class Paypal implements PaymentInterface
{
    private $clientID;
    private $clientSecret;
    private $baseUrl;

    public function __construct()
    {
        $this->clientID = Config::get('payment.paypal.client_id');
        $this->clientSecret = Config::get('payment.paypal.client_secret');
        $this->baseUrl = Config::get('payment.paypal.base_url');
    }

    /**
     * Start the payment process
     *
     * @throws InitiatePaymentException|PaypalException
     */
    public function pay(array $paymentRequestData): array
    {

        Log::channel('payments')->debug("Starting new payment for Paypal", compact('paymentRequestData'));

        $request = Http::asJson()->acceptJson();
        $response = $this->call('v2/checkout/orders', 'POST', ['json' => $paymentRequestData], $request);

        $responseJSON = $response->json();

        if (!isset($responseJSON['status']) && $responseJSON['status'] != 'CREATED') {
            throw new InitiatePaymentException($response->json());
        }

        return $responseJSON;
    }

    /**
     * @throws PaypalException
     * @throws WrongOrderException
     */
    public function getOrderDetails($orderId): array
    {
        $response = $this->call('v2/checkout/orders/' . $orderId, 'GET');
        $responseJSON = $response->json();

        if (!isset($response['status'])) {
            throw new WrongOrderException($response->json());
        }

        return ['success' => true, 'response' => $responseJSON];
    }


    public function capture(string $orderId): array {
        Log::channel('payments')->debug("Capturing order for Paypal", ['orderId' => $orderId]);

        $request = Http::asJson()->acceptJson();
        $response = $this->call('v2/checkout/orders/' . $orderId . '/capture', 'POST', [], $request);
        $responseJSON = $response->json();

        if (!isset($responseJSON['status'])) {
            throw new WrongOrderException($response->json());
        }
        Log::channel('payments')->debug("Captured paypal payment", ['orderID' => $orderId, 'response' => $responseJSON]);

        return $responseJSON;
    }

    public function createSubscription(array $subscriptionCreateData): array {

        Log::channel('payments')->debug("Starting new subscription for Paypal", compact('subscriptionCreateData'));

        $request = Http::asJson()->acceptJson();
        $response = $this->call('v1/billing/subscriptions', 'POST', ['json' => $subscriptionCreateData], $request);
        $responseJSON = $response->json();

        if (!isset($responseJSON['status']) || $responseJSON['status'] != 'APPROVAL_PENDING') {
            throw new PaypalProcessorException($responseJSON);
        }

        Log::channel('payments')->debug("Subscription created", compact('responseJSON'));

        return $responseJSON;
    }

    public function getSubscription(string $subscriptionID): array {
        $request = Http::acceptJson();
        $response = $this->call('v1/billing/subscriptions/' . $subscriptionID, 'GET', [], $request);
        $responseJSON = $response->json();

        if (!isset($responseJSON['status'])) {
            throw new PaypalProcessorException($response->json());
        }

        return $responseJSON;
    }

    public function cancelSubscription(string $subscriptionID, array $cancelData): bool {

        Log::channel('payments')->debug("Cancelling subscription for Paypal", compact('subscriptionID', 'cancelData'));

        $request = Http::asJson();
        $response = $this->call('v1/billing/subscriptions/' . $subscriptionID . '/cancel', 'POST', ['post' => $cancelData], $request);
        if ($response->status() == 204) {
            Log::channel('payments')->debug("Cancelled subscription for Paypal", compact('subscriptionID'));
            return True;
        }

        return False;
    }

    public function activateSubscription(string $subscriptionID, array $activateData): bool {
        Log::channel('payments')->debug("Activating subscription for Paypal", compact('subscriptionID', 'activateData'));

        $request = Http::asJson();
        $response = $this->call('v1/billing/subscriptions/' . $subscriptionID . '/activate', 'POST', ['post' => $activateData], $request);
        if ($response->status() == 204) {
            return True;
        }
        return False;
    }

    public function captureSubscriptionPayment(string $subscriptionID, string $note, array $amount): array {
        if (!array_key_exists('currency_code', $amount) || !array_key_exists('value', $amount)) {
            throw new \Exception('Subscription payment amount is missing required keys');
        }

        $captureData = [
            'note' => $note,
            'capture_type' => 'OUTSTANDING_BALANCE',
            'amount' => $amount
        ];
        $request = Http::asJson()->acceptJson();
        $response = $this->call('v1/billing/subscriptions/' . $subscriptionID . '/capture', 'POST', ['post' => $captureData], $request);
        $responseJSON = $response->json();

        if (!isset($responseJSON['status'])) {
            throw new PaypalProcessorException($response->json());
        }

        return $responseJSON;
    }

    public function getSubscriptionTransactions(string $subscriptionID, string $startTime, string $endTime) {

        $request = Http::acceptJson();
        $params = [
            'query' => [
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ];
        $response = $this->call('v1/billing/subscriptions/' . $subscriptionID . '/transactions', 'GET', $params, $request);

        $responseJSON = $response->json();

        return $responseJSON;
    }

    /**
     * Make cURL calls to PayPal's REST API
     *
     * @throws PaypalException
     */
    private function call(string $url, string $method, array $params = [], ?PendingRequest $request = Null): Response
    {
        if (is_null($request)) {
            $request = new Http();
        }
        $request->withBasicAuth($this->clientID, $this->clientSecret);
        $response = $request->send($method, $this->baseUrl . $url, $params);

        if (!$response->successful()) {
            throw new PaypalException($response->json());
        }

        return $response;
    }

    private function mock(PendingRequest $request) {
        return $request->withHeaders([
            'PayPal-Mock-Response' => '{"mock_application_codes": "DUPLICATE_INVOICE_ID"}'
        ]);
    }

}
