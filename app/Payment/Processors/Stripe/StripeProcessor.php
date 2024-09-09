<?php

namespace App\Payment\Processors\Stripe;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;


class StripeProcessor
{
    private string $authentication;
    protected string $baseUrl;

    public function __construct()
    {
        $this->authentication = Config::get('payment.stripe.token');
        $this->baseUrl = Config::get('payment.stripe.base_url');
    }

    public function getPaymentIntent(string $paymentIntentID): array {
        $paymentIntentResult = $this->call("GET", "v1/payment_intents" . $paymentIntentID);
        $paymentIntentJSON = $paymentIntentResult->json();

        if (!array_key_exists('id', $paymentIntentJSON)) {
            throw new StripeException("Failed to expire Stripe session");
        }

        return $paymentIntentJSON;
    }

    public function createCustomer(): array
    {
        Log::channel('payments')->debug("Creating new Customer");

        $customerCreateResult = $this->call('POST', 'v1/customers');
        $customerCreateJSON = $customerCreateResult->json();

        if (!array_key_exists('id', $customerCreateJSON)) {
            throw new StripeException("Failed to create Stripe customer");
        }
        Log::channel('payments')->info("Created new Stripe customer", ['customerID' => $customerCreateJSON['id']]);

        return $customerCreateJSON;
    }


    public function addInvoiceItem(string $customerID, int $amount, string $currency='usd', ?string $description = Null) {
        $params = [
            "form_params" => [
                'customer' => $customerID,
                'amount' => $amount,
                'currency' => $currency,
            ]
        ];

        if (!is_null($description)) {
            $params['form_params']['description'] = $description;
        }

        Log::channel('payments')->debug("Creating an invoice item", compact('customerID', 'amount', 'currency', 'description'));

        $invoiceItemResult = $this->call('POST', 'v1/invoiceitems', $params);
        $invoiceItemJSON = $invoiceItemResult->json();

        if (!array_key_exists('id', $invoiceItemJSON)) {
            throw new StripeException("Failed to create Stripe invoice item");
        }

        return $invoiceItemJSON;
    }

    public function createSession(array $data): array
    {
        Log::channel('payments')->debug("Creating new Session", compact('data'));
        $params = [
            "form_params" => $data
        ];

        $sessionCreateResult = $this->call('POST', 'v1/checkout/sessions', $params);
        $sessionCreateJSON = $sessionCreateResult->json();

        if (!array_key_exists('id', $sessionCreateJSON)) {
            throw new StripeException("Failed to create Stripe session");
        }

        return $sessionCreateJSON;
    }

    public function expireSession(string $sessionID): array {

        Log::channel('payments')->debug("Expiring Stripe Session", compact('sessionID'));

        $sessionExpireResult = $this->call('POST', 'v1/checkout/sessions/' . $sessionID . '/expire');
        $sessionExpireJSON = $sessionExpireResult->json();

        if (!array_key_exists('id', $sessionExpireJSON)) {
            throw new StripeException("Failed to expire Stripe session");
        }

        return $sessionExpireJSON;
    }


    public function getSession(string $sessionID): array {
        $sessionDetailsResult = $this->call('GET', 'v1/checkout/sessions/' . $sessionID);
        $sessionDetailsJSON = $sessionDetailsResult->json();

        if (!array_key_exists('id', $sessionDetailsJSON)) {
            throw new StripeException("Failed to get Stripe session");
        }

        return $sessionDetailsJSON;
    }

    public function getSubscription(string $subscriptionID): array {
        $subscriptionDetailsResult = $this->call('GET', 'v1/subscriptions/' . $subscriptionID);
        $subscriptionDetailsJSON = $subscriptionDetailsResult->json();

        if (!array_key_exists('id', $subscriptionDetailsJSON)) {
            throw new StripeException("Failed to get Stripe subscription");
        }

        return $subscriptionDetailsJSON;
    }

    public function cancelSubscription($subscriptionID): array {

        Log::channel('payments')->debug("Cancelling subscription", compact('subscriptionID'));
        $cancelledSubscriptionResult = $this->call('DELETE', 'v1/subscriptions/' . $subscriptionID);
        $cancelledSubscriptionJSON = $cancelledSubscriptionResult->json();

        if (!array_key_exists('id', $cancelledSubscriptionJSON)) {
            throw new StripeException("Failed to get Stripe cancelled subscription");
        }

        return $cancelledSubscriptionJSON;
    }


    public function getInvoicesForSubscription(string $subscriptionID): array {

        $params = [
            'query' => [
                'subscription' => $subscriptionID
            ]
        ];

        $invoicesResult = $this->call('GET', 'v1/invoices', $params);
        $invoicesJSON = $invoicesResult->json();

        if (!array_key_exists('object', $invoicesJSON)) {
            throw new StripeException("Failed to get Stripe subscription");
        }

        return $invoicesJSON;
    }

    private function call(string $method, string $url, array $params = []): Response
    {
        $request = new PendingRequest();
        $request->asForm()->acceptJson()->withBasicAuth($this->authentication, '');
        $response = $request->send($method, $this->baseUrl . $url, $params);

        if (!$response->successful()) {
            Log::channel('payments')->error("Stripe query was not successful", compact('method', 'url', 'params', 'response'));
            throw new StripeException($response->body());
        }

        return $response;
    }

}
