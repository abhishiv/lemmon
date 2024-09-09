<?php

namespace App\Payment\Processors;

interface PaymentInterface
{
    public function pay(array $paymentRequestData);

}
