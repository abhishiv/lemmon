<?php

namespace App\Payment\Processors\Paypal;

use App\Payment\Models\PaypalTransaction;
use App\Payment\Processors\PaymentServiceInterface;
use App\Payment\Processors\Paypal\Types\Order;
use Illuminate\Support\Facades\Log;

class PaypalPaymentService implements PaymentServiceInterface
{

    private Paypal $processor;
    private ?int $paymentID;

    protected string $returnURL;
    protected string $cancelURL;
    protected ?string $failedURL;

    const NAME = "Paypal";

    public function __construct(?int $paymentID = null)
    {
        $this->paymentID = $paymentID;
        $this->processor = new Paypal();
    }

    /**
     * Set the return URL for the service
     *
     * @param string $returnURL
     * @param string $cancelURL
     * @param string $failedURL
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
        $creationTransactionRecord = PaypalTransaction::where('payment_id', $paymentID)
            ->where('status', PaypalTransaction::TO_CREATED)->first();
        if ($creationTransactionRecord) {
            $createResponseRecord = $creationTransactionRecord->response;

            foreach ($createResponseRecord['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return $link['href'];
                }
            }
        }
        return null;
    }

    public function setPaymentID(int $paymentID)
    {
        $this->paymentID = $paymentID;
    }


    public function initiatePayment($item): array
    {
        $order = new Order($this->returnURL, $this->cancelURL);
        $paymentRequestData = $order->payment($item);

        $transaction = new PaypalTransaction([
            'payment_id' => $this->paymentID,
            'payload' => json_encode($paymentRequestData)
        ]);

        try {
            $response = $this->processor->pay($paymentRequestData);
        } catch (\Exception $e) {
            $transaction->status = PaypalTransaction::TO_FAILED;
            $transaction->save();
            throw $e;
        }

        try {
            $transaction->response = json_encode($response);
        } catch (\Exception $e) {
            $transaction->response = null;
        }

        if (!array_key_exists('status', $response)) {
            Log::channel('payments')->error("Failed to get status key from paypal response");
            $transaction->status = PaypalTransaction::TO_FAILED;
            $transaction->save();
            throw new PaypalException('Response is missing status key');
        }

        $responseStatus = $response['status'];

        if ($responseStatus != "CREATED") {
            Log::channel('payments')->error("paypal response status is not CREATED");
            $transaction->status = PaypalTransaction::TO_FAILED;
            $transaction->save();
            throw new PaypalException('Response status is not CREATED');
        }

        $responseID = $response['id'] ?? null;

        $transaction->status = PaypalTransaction::TO_CREATED;
        $transaction->token = $responseID;
        $transaction->save();
        Log::channel('payments')->debug("Payment is now created");

        return $response;
    }

    public function getTransactionID(): mixed
    {
        $creationTransactionRecord = PaypalTransaction::where('payment_id', $this->paymentID)
            ->where('status', PaypalTransaction::TO_CREATED)->first();

        if ($creationTransactionRecord) {
            return $creationTransactionRecord->token;
        }
        return null;
    }

    public function confirmRedirect(array $data): bool
    {
        if (is_null($this->paymentID)) {
            throw new PaypalException('Payment ID is not initialized');
        }

        $payerID = $data['PayerID'] ?? null;

        if (is_null($payerID)) {
            Log::channel('payments')->warning("Paypal Payer ID could not be collected on return");
        }

        # Check if the payment is already confirmed
        if (PaypalTransaction::where('payment_id', $this->paymentID)
                ->where('status', PaypalTransaction::TO_RETURNED)->exists() === true) {
            return false;
        }

        PaypalTransaction::create([
            'payment_id' => $this->paymentID,
            'payer_id' => $payerID,
            'status' => PaypalTransaction::TO_RETURNED,
            'response' => json_encode($data)
        ]);

        return true;
    }

    public function confirmPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new PaypalException('Payment ID is not initialized');
        }

        $transaction = new PaypalTransaction([
            'payment_id' => $this->paymentID
        ]);

        $transactionID = $this->getTransactionID();
        if (is_null($transactionID)) {
            throw new PaypalException('Transaction ID is not found');
        }

        try {
            $response = $this->processor->capture($transactionID);
        } catch (\Exception $e) {
            $transaction->status = PaypalTransaction::TO_FAILED;
            $transaction->save();
            throw $e;
        }

        $transaction->response = json_encode($response);

        if (array_key_exists('status', $response)) {
            if ($response['status'] === 'COMPLETED') {
                $transaction->status = PaypalTransaction::TO_CAPTURED;
                $transaction->save();
                return true;
            } else {
                Log::channel('payments')->error("Capture status is not completed", ['response' => $response]);
                $transaction->status = PaypalTransaction::TO_FAILED;
                $transaction->save();
                return false;
            }
        } else {
            Log::channel('payments')->error("Invalid response content", ['response' => $response]);
            $transaction->status = PaypalTransaction::TO_FAILED;
            $transaction->save();
            return false;
        }
    }

    public function cancelPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new PaypalException('Payment ID is not initialized');
        }

        Log::channel('payments')->info("Cancelling payment", ['id' => $this->paymentID]);

        PaypalTransaction::create([
            'payment_id' => $this->paymentID,
            'status' => PaypalTransaction::TO_CANCELLED,
            'response' => json_encode(['message' => 'cancelled by request']),
        ]);
        return true;
    }
}
