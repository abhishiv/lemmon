<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Restaurant;
use App\Payment\Models\Payment;
use Illuminate\Console\Command;
use App\Http\Services\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Payment\Models\PayrexxTransaction;
use Illuminate\Http\Client\PendingRequest;
use App\Payment\Exceptions\PayrexxException;

class CheckPaymentConfirmations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private mixed $baseUrl;

    public $orderId = null;
    public $sessionID = null;
    public $restaurantID = null;
    public $restaurantTokenName = null;
    public $restaurantToken = null;
    public ?int $paymentID = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $restaurants = Restaurant::get();

        foreach ($restaurants as $restaurant) {
            $this->restaurantID = $restaurant->id;
            $this->restaurantTokenName = $restaurant->payrexx_name;
            $this->restaurantToken = $restaurant->payrexx_token;
            $this->getOrders();
        }

        return Command::SUCCESS;
    }

    public function getOrders()
    {
        $orders = Order::where('restaurant_id', $this->restaurantID)
                    ->where('payment_method', Order::ONLINE)
                    ->where('status', Order::INITIAL)
                    ->where('created_at', '>', now()->subMinutes(15))
                    ->get();

        foreach ($orders as $order) {
            $this->orderId = $order->id;
            $this->sessionID = $order->session_id;
            $this->getPaymentsFromOrder();
        }
    }


    public function getPaymentsFromOrder()
    {
        $payments = Payment::where('order_id', $this->orderId)->get();

        foreach ($payments as $payment) {
            $this->checkPayment($payment->id);
        }
    }


    /**
     * @throws PayrexxException
     */
    public function checkPayment($paymentID)
    {
        $this->baseUrl = Config::get('payment.payrexx.base_url');

        if (is_null($paymentID)) {
            throw new PayrexxException('CRON Payment ID is not initialized');
        }

        $transaction = new PayrexxTransaction([
            'payment_id' => $paymentID,
            'reference_id'  => $this->orderId,
        ]);

        $transactionID = $this->getTransactionID($paymentID);
        if (is_null($transactionID)) {
            throw new PayrexxException('CRON Transaction ID is not found, payment_id is: ' . print_r($paymentID, true));
        }

        try {
            $response = $this->capture($transactionID);
        } catch (\Exception $e) {
            Log::channel('payments')->warning("CRON Failed during payment capture. Exception: " . print_r($e, true));
            throw $e;
        }

        $transaction->response = json_encode($response);
        $responseBody = $response['data'][0];

        Log::channel('payments')->info("CRON Confirm payment, payment capture response: " . print_r($responseBody, true) . "CRON Payment response status: " . print_r($response['data'][0]['status'], true));

        if (array_key_exists('status', $responseBody)) {
            if ($responseBody['status'] !== 'confirmed') {
                return;
            }
            $transaction->status = PayrexxTransaction::TO_COMPLETE;
            $transaction->save();

            $orderService = new OrderService();
            Payment::where('id', $paymentID)->update(['status' => Payment::CONFIRMED]);
            $orderService->setSessionOrders($transaction->reference_id, $this->sessionID);
            return true;
        }
    }

    public function getTransactionID($paymentID): mixed
    {
        $creationTransactionRecord = PayrexxTransaction::where('payment_id', $paymentID)
            ->where('status', PayrexxTransaction::TO_CREATED)->first();

        if ($creationTransactionRecord) {
            return $creationTransactionRecord->token;
        }
        return null;
    }

    public function capture(string $transactionID): array {
        Log::channel('payments')->debug("CRON Capturing order for Payrexx", ['orderId' => $transactionID]);

        $request = Http::asForm();

        $response = $this->callPayment('Gateway/' . $transactionID, 'GET', [], $request);

        $responseJSON = $response->json();

        if (!isset($responseJSON['status'])) {
            throw PayrexxException::WrongOrderException($response->json());
        }
        Log::channel('payments')->debug("CRON Captured payrexx payment", ['orderID' => $transactionID, 'response' => $responseJSON]);

        return $responseJSON;
    }

    private function callPayment(string $url, string $method, array $params = [], ?PendingRequest $request = null): Response
    {
        if (is_null($request)) {
            $request = new Http();
        }

        $params['ApiSignature'] = base64_encode(hash_hmac('sha256', http_build_query($params, null, '&'),
            $this->restaurantToken, true));

        $query['instance'] = $this->restaurantTokenName;

        $response = $request->send($method, $this->baseUrl . $url . '/?' . http_build_query($query, null, '&'), ['form_params' => $params]);

        if (!$response->successful()) {
            throw PayrexxException::InvalidRequestException($response);
        }

        return $response;
    }
}
