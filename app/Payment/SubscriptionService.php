<?php

namespace App\Payment;

use App\Models\OrderItem;
use App\Payment\Processors\Paypal\PaypalSubscriptionService;
use App\Payment\Processors\Stripe\StripeSubscriptionService;
use App\Payment\Processors\SubscriptionServiceInterface;
use Carbon\Carbon;

final class SubscriptionService
{

    private SubscriptionServiceInterface $subscriptionProcessorService;
    private $type;

    public function __construct(string $subscriptionType)
    {
        $this->type = $subscriptionType;
        if ($this->type === 'Paypal') {
            $this->subscriptionProcessorService = new PaypalSubscriptionService();
        } elseif ($this->type === 'Stripe') {
            $this->subscriptionProcessorService = new StripeSubscriptionService();
        } else {
            throw new \Exception('Unknown payment type');
        }
    }

    public function createSubscription(OrderItem $item, string $subscriptionPlan, mixed $customID) {
        $subscriptionResponse = $this->subscriptionProcessorService->create($item, $subscriptionPlan, $customID);
        return $subscriptionResponse;
    }

    public function getApprovalURL(string $subscriptionID): ?string {
        return $this->subscriptionProcessorService->getApprovalURL($subscriptionID);
    }

    public function cancelSubscription(string $subscriptionID, string $reason): bool {
        return $this->subscriptionProcessorService->cancelSubscription($subscriptionID, $reason);
    }

    public function getSubscriptionData(string $subscriptionID): array {
        return $this->subscriptionProcessorService->getSubscriptionData($subscriptionID);
    }

    public function getPaymentHistory(string $subscriptionID, ?Carbon $startDate = Null, ?Carbon $endDate = Null): array {
        return $this->subscriptionProcessorService->getPaymentHistory($subscriptionID, $startDate, $endDate);
    }

    public function setReturnURL(string $returnURL, string $cancelURL) {
        $this->subscriptionProcessorService->setReturnURL($returnURL, $cancelURL);
    }
}
