<?php

namespace App\Payment\Exceptions;

use Exception;
use Illuminate\http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PayrexxException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        Log::channel('payments')->error('Payrexx Error construct ' . print_r($message, true) . print_r(Request::capture()->request->all(), true));

        parent::__construct('Payment Error from construct!', $code, $previous);
    }

    public static function InvalidRequestException(Response $response): static
    {
        $responseBody = $response->json();

        Log::channel('payments')->error('Payrexx Error invalid request exception ' . print_r($responseBody, true) . print_r(Request::capture()->request->all(), true));

        return new static('Payment error invalid request exception!', $response->status());
    }

    public static function InitiatePaymentException(Response $response): static
    {
        $responseBody = $response->json();

        Log::channel('payments')->error('Payrexx Error initiate payment exception ' . print_r($responseBody, true) . print_r(Request::capture()->request->all(), true));

        return new static('Payment initiation failed!', $response->status());
    }

    public static function ResponseStatusMissing(): static
    {
        Log::channel('payments')->error("Failed to get status key from payrexx response");

        return new static('Payment initiation failed!', 500);
    }

    public static function WrongResponseStatus():static
    {
        Log::channel('payments')->error("Payrexx response status is not 'waiting'");

        return new static('Payment failed!', 500);
    }

    public static function NoPaymentID():static
    {
        Log::channel('payments')->error("Payment ID is not initialized");

        return new static('Payment not found!', 500);
    }

    public static function InvalidRedirectLink(): static
    {
        Log::channel('payments')->error("Payment redirect link is missing");

        return new static('Payment redirect link is missing', 500);

    }

    public static function WrongOrderException(Response $response):static
    {
        $responseBody = $response->json();

        Log::channel('payments')->error('Payrexx Error wrongorderexpcetion ' . print_r($responseBody, true) . print_r(Request::capture()->request->all(), true));

        return new static('Wrong order!', $response->status());
    }

}
