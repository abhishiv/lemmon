<?php

namespace App\Payment\Processors\Stripe;

use App\Models\OrderItem;
use App\Payment\Processors\BasicPaymentService;
use App\Payment\Processors\SubscriptionServiceInterface;
use Carbon\Carbon;

class StripeSubscriptionService implements SubscriptionServiceInterface {

    private $processor;

    public function __construct() {
        parent::__construct();
        $this->processor = new StripeProcessor();
    }

    public function setReturnURL(string $returnURL, string $cancelURL, ?string $failedURL): void
    {
        $this->returnURL = $returnURL;
        $this->cancelURL = $cancelURL;
        $this->failedURL = $failedURL;
    }

    public static function extractRedirectURL(array $sessionData): ?string {
        if (array_key_exists('url', $sessionData)) {
            return $sessionData['url'];
        }
    }

    public function create(OrderItem $item, string $subscriptionPlan, mixed $customID, ?string $customerEmail = Null): array
    {
        $order = new Order($this->returnURL, $this->cancelURL);
        $subscriptionRequest = $order->subscription($item);

        if (!is_null($customerEmail)) {
            $subscriptionRequest['customer_email'] = $customerEmail;
        }

        $subscriptionResponse = $this->processor->createSession($subscriptionRequest);
        $redirectURL = self::extractRedirectURL($subscriptionResponse);

        return [
            'id' => $subscriptionResponse['id'],
            'status' => $subscriptionResponse['status'],
            'redirectURL' => $redirectURL
        ];
    }

    public function getApprovalURL(string $subscriptionID): ?string {
        $subscriptionResponse = $this->processor->getSession($subscriptionID);
        return self::extractRedirectURL($subscriptionResponse);
    }

    public function cancelSubscription(string $sessionID, string $reason): bool {
        $subscriptionID = $this->getSubscriptionFromSession($sessionID);

        if (is_null($subscriptionID)) {

            # Session has no subscription, cancel the session instead
            $cancelResult = $this->processor->expireSession($sessionID);
            return $cancelResult['status'] === 'expired';
        }

        $subscriptionData = $this->processor->getSubscription($subscriptionID);

        if ($subscriptionData['status'] === 'canceled' ||
            $subscriptionData['status'] === 'incomplete_expired')
            return True;

        $cancelResult = $this->processor->cancelSubscription($subscriptionID);

        return $cancelResult['status'] === 'canceled';
    }

    public function getSubscriptionFromSession(string $sessionID): ?string {
        $sessionData = $this->getSessionData($sessionID);
        return $sessionData['subscription'];
    }

    public function getSessionData(string $sessionID): array
    {
        return $this->processor->getSession($sessionID);
    }

    public function getSubscriptionData(string $sessionID): array
    {
        return $this->getSubscriptionDataFromSession($sessionID);
    }

    public function getSubscriptionDataFromSession(string $sessionID): array
    {
        $subscriptionID = $this->getSubscriptionFromSession($sessionID);
        if (is_null($subscriptionID)) {
            # Session has no subscription
            throw new StripeException("Session has no subscription", 10);
        }

        return $this->processor->getSubscription($subscriptionID);
    }

    public function getPaymentHistory(string $subscriptionID, ?Carbon $startDate = Null, ?Carbon $endDate = Null): array {

        $invoiceList = $this->processor->getInvoicesForSubscription($subscriptionID);

        if (!array_key_exists('data', $invoiceList)) {
            return [];
        }

        return array_map(function ($invoice) {
            return [
                'status' => $invoice['paid'] === True,
                'id' => $invoice['id'],
                'amount' => floatval($invoice['amount_paid']) / 100,
                'currency' => $invoice['currency'],
                'timestamp' => $invoice['status_transitions']['paid_at'] ? Carbon::createFromTimestamp($invoice['status_transitions']['paid_at']) : Null,
            ];
        }, $invoiceList['data']);
    }
}
