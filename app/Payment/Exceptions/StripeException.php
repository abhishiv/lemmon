<?php

namespace App\Payment\Exceptions;

use Illuminate\http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeException extends PaymentException
{
    /*
     * An error occurred during payment involving one of these situations:
     *
     * Payment blocked for suspected fraud
     * Payment declined by the issuer
     * Other payment errors
     */
    public static function CardException(Response $response): void
    {
        $response = $response->json();

        (new PaymentException)->__construct('Payment Stripe error!', $response->status());

        Log::channel('payments')->error('Stripe Error' . print_r($response, true) . print_r(Request::capture()->all(),
                true));

    }
}
