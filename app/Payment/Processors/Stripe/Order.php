<?php

namespace App\Payment\Processors\Stripe;

use App\Models\OrderItem;

class Order
{
    private $payment;
    private $cancelUrl;
    private $returnUrl;

    public function __construct(string $returnURL, string $cancelURL) {
        $this->cancelUrl = $cancelURL;
        $this->returnUrl = $returnURL;
    }

    public function payment(OrderItem $item): array
    {
        $this->payment = [
            'payment_method_types' => ['card'],
            'cancel_url' => $this->cancelUrl . '?token={CHECKOUT_SESSION_ID}',
            'mode' => 'payment',
            'success_url' => $this->returnUrl . '?token={CHECKOUT_SESSION_ID}',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $item->getCurrency(),
                        'product_data' => [
                            'name' => 'Test'
                        ],
                        'unit_amount_decimal' => round($item->getUnitAmount() * 100),
                    ],
                    'quantity' => $item->getQuantity(),
                ]
            ]
        ];
        return $this->payment;
    }

    public function subscription(OrderItem $item): array
    {
        if (isset($item->recurrence)) {
            if ($item->recurrence === 'monthly') {
                $interval = 'month';
            } elseif ($item->recurrence === 'yearly') {
                $interval = 'year';
            } else {
                $interval = Null;
            }
        }

        $subscription = [
            'payment_method_types' => ['card'],
            'cancel_url' => $this->cancelUrl . '?subscription_id={CHECKOUT_SESSION_ID}',
            'mode' => 'subscription',
            'success_url' => $this->returnUrl . '?subscription_id={CHECKOUT_SESSION_ID}',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $item->getCurrency(),
                        'product_data' => [
                            'name' => 'Test'
                        ],
                        'unit_amount_decimal' => $item->getOriginalUnitAmount() * 100,
                        'recurring' => [
                            'interval' => $interval,
                            'interval_count' => 1
                        ]
                    ],
                    'quantity' => $item->getQuantity(),
                ]
            ]
        ];

        if ($item->hasDiscount()) {
            $subscription['discounts'] = [
                [
                    'coupon' => $item->getDiscountCode()
                ]
            ];
        }

        return $subscription;
    }


    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
