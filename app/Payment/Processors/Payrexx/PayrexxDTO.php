<?php

namespace App\Payment\Processors\Payrexx;

use Illuminate\Support\Facades\Config;

class PayrexxDTO
{
    private $currency;

    public function __construct(
        private string $returnURL,
        private string $cancelURL,
        private ?string $failedURL,
        private array $payment = []
    ) {
        $this->currency = Config::get('payment.payrexx.currency');
    }

    public function payment($order): array
    {
        $this->payment = [
            'amount' => bcmul($order->totalAmount, 100, 0),
            'currency' => $this->currency,
            'basket' => [],
            'referenceId' => $order->id,
            'successRedirectUrl' => $this->returnURL,
            'cancelRedirectUrl' => $this->cancelURL,
            'failedRedirectUrl' => $this->failedURL,
        ];
        foreach ($order->items as $key => $item) {
            $this->payment['basket'][$key]['name'][] = $item->products->name;
            $this->payment['basket'][$key]['description'][] = $item->products->name;
            $this->payment['basket'][$key]['quantity'] = $item->quantity;
            $this->payment['basket'][$key]['amount'] = (int)($item->price * 100);
        }
        $this->payment['basket'] = http_build_query($this->payment['basket'], null, '&');

        return $this->payment;
    }

    public function subscription($planID, ?string $customID = null, ?array $customPlan = null): array
    {
        $subscription = [];

        return $subscription;
    }


    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
