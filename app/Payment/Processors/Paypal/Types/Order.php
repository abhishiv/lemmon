<?php

namespace App\Payment\Processors\Paypal\Types;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Config;

class Order
{
    private $currency;
    private $payment;
    private $cancelUrl;
    private $returnUrl;
    private $config;

    public function __construct(string $returnURL, string $cancelURL) {
        $this->payment = [];
        $this->config = Config::get('payment.paypal');
        $this->currency = $this->config['currency'];

        $this->cancelUrl = $cancelURL;
        $this->returnUrl = $returnURL;
    }

    public function payment(Item $item): array
    {
        $this->payment = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $item->getIdentifier() ?: 'default',
                    'description' => $item->getName(),
                    'amount' => [
                        'value' => $item->getAmount(),
                        'currency_code' => $this->currency
                    ]
                ]
            ],
            'application_context' => [
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl
            ]
        ];

        return $this->payment;
    }

    public function subscription($planID, ?string $customID = Null, ?array $customPlan = Null): array
    {
        $subscription = [
            'plan_id' => $planID,
            'application_context' => [
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => $this->returnUrl,
                'cancel_url' => $this->cancelUrl
            ]
        ];

        if (!is_null($customID)) {
            $subscription['custom_id'] = $customID;
        }

        if (!is_null($customPlan)) {
            $subscription['plan'] = $customPlan;
        }

        return $subscription;
    }


    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
