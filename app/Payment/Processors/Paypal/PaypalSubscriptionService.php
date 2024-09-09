<?php

namespace App\Payment\Processors\Paypal;

use App\Models\OrderItem;
use App\Payment\Processors\BasicPaymentService;
use App\Payment\Processors\Paypal\Types\Order;
use App\Payment\Processors\SubscriptionServiceInterface;
use Carbon\Carbon;

class PaypalSubscriptionService extends BasicPaymentService implements SubscriptionServiceInterface {

    private $processor;

    public function __construct() {
        parent::__construct();
        $this->processor = new Paypal();
    }

    static protected function extractRedirectURL(array $links): ?string {
        foreach ($links as $link) {
            if (array_key_exists('rel', $link) && array_key_exists('href', $link) && $link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return Null;
    }

    public function create(OrderItem $item, string $subscriptionPlan, mixed $customID, ?string $customerEmail = Null): array
    {
        $customPlan = [
            'billing_cycles' => [
                [
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'currency_code' => strtoupper($item->getCurrency()),
                            'value' => $item->getAmount(),
                        ],
                    ],
                    'sequence' => 1,
                    'total_cycles' => 1
                ],
                [
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'currency_code' => strtoupper($item->getCurrency()),
                            'value' => $item->getOriginalAmount(),
                        ],
                    ],
                    'sequence' => 2,
                    'total_cycles' => 0
                ]
            ],
        ];
        $order = new Order($this->returnURL, $this->cancelURL);
        $subscriptionRequest = $order->subscription($subscriptionPlan, $customID, $customPlan);
        $subscriptionResponse = $this->processor->createSubscription($subscriptionRequest);
        $redirectURL = self::extractRedirectURL($subscriptionResponse['links'] ?? []);

        return [
            'id' => $subscriptionResponse['id'],
            'status' => $subscriptionResponse['status'],
            'redirectURL' => $redirectURL
        ];
    }

    public function getApprovalURL(string $subscriptionID): ?string {
        $subscriptionResponse = $this->processor->getSubscription($subscriptionID);
        return self::extractRedirectURL($subscriptionResponse['links'] ?? []);
    }

    public function cancelSubscription(string $subscriptionID, string $reason): bool {

        $subscriptionData = $this->processor->getSubscription($subscriptionID);
        if ($subscriptionData['status'] === 'CANCELLED') return True;
        if ($subscriptionData['status'] === 'APPROVAL_PENDING') return True;

        return $this->processor->cancelSubscription($subscriptionID, ['reason' => $reason]);
    }

    public function activateSubscription(string $subscriptionID, ?string $reason = Null): bool {

        if (is_null($reason)) {
            $reason = 'Starting subscription';
        }
        try {
            return $this->processor->activateSubscription($subscriptionID, ['reason' => $reason]);
        } catch (\Exception $e) {
            return False;
        }
    }

    public function captureSubscription(string $subscriptionID, float $amount, ?string $reason): array {
        if (is_null($reason)) {
            $reason = 'Capturing confirmed subscription';
        }
        $amount = [
            'currency_code' => 'USD',
            'value' => $amount,
        ];

        return $this->processor->captureSubscriptionPayment($subscriptionID, $reason, $amount);
    }

    public function getSubscriptionData(string $subscriptionID): array
    {
        return $this->processor->getSubscription($subscriptionID);
    }

    public function getPaymentHistory(string $subscriptionID, ?Carbon $startDate = Null, ?Carbon $endDate = Null): array {

        if (is_null($endDate)) {
            $endDate = Carbon::now();
        }

        if (is_null($startDate)) {
            $startDate = $endDate->subYear();
        }

        $startTime = $startDate->toIso8601ZuluString();
        $endTime = $endDate->toIso8601ZuluString();

        $transactionResults = $this->processor->getSubscriptionTransactions($subscriptionID, $startTime, $endTime);

        if (!array_key_exists('transactions', $transactionResults)) {
            return [];
        }

        return array_map(function ($transaction) {
            return [
                'status' => $transaction['status'] === 'COMPLETED',
                'id' => $transaction['id'],
                'amount' => floatval($transaction['amount_with_breakdown']['gross_amount']['value']),
                'currency' => $transaction['amount_with_breakdown']['gross_amount']['currency_code'],
                'timestamp' => Carbon::createFromFormat('Y-m-d\TH:i:s\.u\Z', $transaction['time'])
            ];
        }, $transactionResults['transactions']);
    }
}
