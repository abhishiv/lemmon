<?php

namespace App\Payment\Processors\Paypal;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;

class WrongOrderException extends Exception
{

    private $response;

    public function __construct($response)
    {
        parent::__construct();
        $this->response = $response;
    }

    public function report(Request $request)
    {
        Log::channel('payments')->error('Paypal Order Error' . print_r($this->response, true) . print_r($request->all(), true));
    }

    public function render($request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Wrong order id']);
    }
}
