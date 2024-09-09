<?php


namespace App\Payment\Exceptions;

use Exception;
use Illuminate\http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalException extends Exception
{

    /*
     * An error ocurred durring payment involving one of these situations:
     *
     * Payment blocked for suspected fraud
     * Payment declined by the issuer
     * Other payment errors
     */
    public static function invalidRequestException(Response $response): static
    {
        $responseBody = $response->json();

        Log::channel('payments')->error('Paypal Error' . print_r($responseBody,
                true) . print_r(Request::capture()->request->all(), true));

        return new static('Payment invalid request Error', $response->status());
    }
}
