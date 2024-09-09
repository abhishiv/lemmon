<?php

namespace App\Payment\Exceptions;

use Exception;
use Throwable;

class PaymentException extends Exception {

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
