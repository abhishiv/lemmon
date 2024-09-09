<?php

namespace App\Payment\Processors\Payrexx;

use App\Models\Restaurant;
use App\Payment\Processors\PaymentInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Payment\Exceptions\PayrexxException;

final class Payrexx implements PaymentInterface
{
    private mixed $clientID;
    private mixed $clientSecret;
    private mixed $baseUrl;

    public function __construct()
    {
        $restaurant = Restaurant::findOrFail(session('restaurant.id'));
//        $this->clientID = Config::get('payment.payrexx.instance_name');
//        $this->clientSecret = Config::get('payment.payrexx.secret');
        $this->clientID = $restaurant->payrexx_name;
        $this->clientSecret = $restaurant->payrexx_token;
        $this->baseUrl = Config::get('payment.payrexx.base_url');
    }

    /**
     * @throws PayrexxException
     */
    public function pay(array $paymentRequestData): array
    {
        Log::channel('payments')->debug("Starting new payment for Payrexx", compact('paymentRequestData'));

        $request = Http::asForm();

        $response = $this->call('Gateway', 'POST', $paymentRequestData, $request);

        $responseJSON = $response->json();

        if (!isset($responseJSON['status']) && $responseJSON['status'] != 'CREATED') {
            throw PayrexxException::InitiatePaymentException($response);
        }

        return $responseJSON;
    }

    public function capture(string $transactionID): array {
        Log::channel('payments')->debug("Capturing order for Payrexx", ['orderId' => $transactionID]);

        $request = Http::asForm();

        $response = $this->call('Gateway/' . $transactionID, 'GET', [], $request);

        $responseJSON = $response->json();

        if (!isset($responseJSON['status'])) {
            throw PayrexxException::WrongOrderException($response->json());
        }
        Log::channel('payments')->debug("Captured payrexx payment", ['orderID' => $transactionID, 'response' => $responseJSON]);

        return $responseJSON;
    }

    /**
     * Make cURL calls to Payrexx's REST API
     *
     * @throws PayrexxException
     */
    private function call(string $url, string $method, array $params = [], ?PendingRequest $request = null): Response
    {
        if (is_null($request)) {
            $request = new Http();
        }

        $params['ApiSignature'] = base64_encode(hash_hmac('sha256', http_build_query($params, null, '&'),
            $this->clientSecret, true));

        $query['instance'] = $this->clientID;

        $response = $request->send($method, $this->baseUrl . $url . '/?' . http_build_query($query, null, '&'), ['form_params' => $params]);

        if (!$response->successful()) {
            throw PayrexxException::InvalidRequestException($response);
        }

        return $response;
    }

    private function mock(PendingRequest $request)
    {
        return $request->withHeaders([
            'Payrexx-Mock-Response' => '{"mock_application_codes": "DUPLICATE_INVOICE_ID"}'
        ]);
    }

}
