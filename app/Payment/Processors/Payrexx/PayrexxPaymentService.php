<?php

namespace App\Payment\Processors\Payrexx;

use App\Models\Order;
use App\Payment\Exceptions\PayrexxException;
use App\Payment\Models\Payment;
use App\Payment\Models\PayrexxTransaction;
use App\Payment\Processors\PaymentServiceInterface;
use Illuminate\Support\Facades\Log;

class PayrexxPaymentService implements PaymentServiceInterface
{

    private Payrexx $processor;
    private ?int $paymentID;

    protected string $returnURL;
    protected string $cancelURL;
    protected string $failedURL;

    const NAME = "Payrexx";

    public function __construct(?int $paymentID = null)
    {
        $this->paymentID = $paymentID;
        $this->processor = new Payrexx();
    }

    /**
     * Set the return URL for the service
     *
     * @param string $returnURL
     * @param string $cancelURL
     * @param string|null $failedURL
     */
    public function setReturnURL(string $returnURL, string $cancelURL, ?string $failedURL): void
    {
        $this->returnURL = $returnURL;
        $this->cancelURL = $cancelURL;
        $this->failedURL = $failedURL;
    }

    public function getApproveRedirectURL(?int $paymentID): ?string
    {
        if (is_null($paymentID)) {
            $paymentID = $this->paymentID;
        }
        $creationTransactionRecord = PayrexxTransaction::where('payment_id', $paymentID)
            ->where('status', PayrexxTransaction::TO_CREATED)->first();
        if ($creationTransactionRecord) {
            $createResponseRecord = $creationTransactionRecord->response;

            if(empty($createResponseRecord['link'])){
                throw PayrexxException::InvalidRedirectLink();
            }
            return $createResponseRecord['link'];
        }
        return null;
    }

    /**
     * Set the payment ID
     *
     * @param int $paymentID
     * @return void
     */
    public function setPaymentID(int $paymentID): void
    {
        $this->paymentID = $paymentID;
    }

    /**
     * @param Order $order
     * @return array
     * @throws PayrexxException
     */
    public function initiatePayment(Order $order): array
    {
        $paymentRequest = new PayrexxDTO($this->returnURL, $this->cancelURL, $this->failedURL);

        $paymentRequestData = $paymentRequest->payment($order);

        $transaction = new PayrexxTransaction([
            'payment_id' => $this->paymentID,
            'payload' => json_encode($paymentRequestData),
            'reference_id' => $order->id
        ]);

        try {
            $response = $this->processor->pay($paymentRequestData);
        } catch (\Exception $e) {
            Log::channel('payments')->warning("Failed to start payment. Exception: " . print_r($e, true));
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->response = null;
            $transaction->save();
            throw $e;
        }
        $responseBody = $response['data'][0];

        try {
            $transaction->response = json_encode($responseBody);
            Log::channel('payments')->debug("Payrexx payment response:" . print_r($transaction->response, true));
        } catch (\Exception $e) {
            Log::channel('payments')->warning("Failed to parse payrexx payment response. Exception: " . print_r($e, true));
            $transaction->response = null;
        }

        if (!array_key_exists('status', $responseBody)) {
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            throw  PayrexxException::ResponseStatusMissing();
        }

        if ($responseBody['status'] != "waiting") {
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            throw PayrexxException::WrongResponseStatus();
        }

        $responseID = $responseBody['id'] ?? null;

        $transaction->status = PayrexxTransaction::TO_CREATED;
        $transaction->token = $responseID;
        $transaction->save();

        Log::channel('payments')->debug("Payment is now created");

        return $responseBody;
    }

    public function getTransactionID(): mixed
    {
        $creationTransactionRecord = PayrexxTransaction::where('payment_id', $this->paymentID)
            ->where('status', PayrexxTransaction::TO_CREATED)->first();

        if ($creationTransactionRecord) {
            return $creationTransactionRecord->token;
        }
        return null;
    }

    public function confirmRedirect(array $data): bool
    {
        if (is_null($this->paymentID)) {
            throw PayrexxException::NoPaymentID();
        }
        $token = $data['token'] ?? null;

        if (is_null($token)) {
            Log::channel('payments')->warning("Payrexx Token could not be collected on return");
        }

        # Check if the payment is already confirmed
        if (PayrexxTransaction::where('payment_id', $this->paymentID)
                ->where('status', PayrexxTransaction::TO_RETURNED)->exists() === true) {
            Log::channel('payments')->warning("Payrexx transaction is already created!");
            return false;
        }

        PayrexxTransaction::create([
            'payment_id' => $this->paymentID,
            'token' => $token,
            'reference_id' => $data['reference_id'],
            'status' => PayrexxTransaction::TO_RETURNED,
            'response' => json_encode(['Success'])
        ]);

        return true;
    }

    /**
     * @throws PayrexxException
     */
    public function confirmPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new PayrexxException('Payment ID is not initialized');
        }

        $transaction = new PayrexxTransaction([
            'payment_id' => $this->paymentID,
            'reference_id'  => Payment::find($this->paymentID)->order_id,
        ]);

        $transactionID = $this->getTransactionID();
        if (is_null($transactionID)) {
            throw new PayrexxException('Transaction ID is not found');
        }

        try {
            $response = $this->processor->capture($transactionID);
        } catch (\Exception $e) {
            Log::channel('payments')->warning("Failed during payment capture. Exception: " . print_r($e, true));

            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            throw $e;
        }

        $transaction->response = json_encode($response);
        $responseBody = $response['data'][0];

        Log::channel('payments')->info("Confirm payment, payment capture response: " . print_r($responseBody, true) . "Payment response status: " . print_r($response['data'][0]['status'], true));

        if (array_key_exists('status', $responseBody)) {
            if ($responseBody['status'] === 'confirmed') {
                $transaction->status = PayrexxTransaction::TO_COMPLETE;
                $transaction->save();
                return true;
            } else {
                Log::channel('payments')->error("Capture status is not completed", ['response' => $response]);
                $transaction->status = PayrexxTransaction::TO_FAILED;
                $transaction->save();
                return false;
            }
        } else {
            Log::channel('payments')->error("Invalid response content", ['response' => $response]);
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            return false;
        }
    }

    /**
     * @throws PayrexxException
     */
    public function cancelPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new PayrexxException('Payment ID is not initialized');
        }

        Log::channel('payments')->info("Cancelling payment", ['id' => $this->paymentID]);

        PayrexxTransaction::create([
            'payment_id' => $this->paymentID,
            'status' => PayrexxTransaction::TO_CANCELLED,
            'response' => json_encode(['message' => 'cancelled by request']),
        ]);
        return true;
    }
}
