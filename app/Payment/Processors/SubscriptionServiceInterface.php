<?php

namespace App\Payment\Processors;

use App\Models\OrderItem;
use Carbon\Carbon;

interface SubscriptionServiceInterface
{
    public function create(OrderItem $item, string $subscriptionPlan, mixed $customID, ?string $customerEmail);

    public function setReturnURL(string $returnURL, string $cancelURL, ?string $failedURL);

    public function getApprovalURL(string $subscriptionID): ?string;

    public function cancelSubscription(string $subscriptionID, string $reason): bool;

    public function getSubscriptionData(string $subscriptionID): array;

    public function getPaymentHistory(string $subscriptionID, ?Carbon $startDate, ?Carbon $endDate): array;

}
