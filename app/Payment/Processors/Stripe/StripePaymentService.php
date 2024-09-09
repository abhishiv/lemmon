<?php

namespace App\Payment\Processors\Stripe;

use App\Models\OrderItem;
use App\Payment\Models\PayrexxTransaction;
use App\Payment\Processors\BasicPaymentService;
use App\Payment\Processors\PaymentServiceInterface;
use Illuminate\Support\Facades\Log;

class StripePaymentService implements PaymentServiceInterface {

    private $processor;
    const NAME = "Stripe";

    public function __construct(?int $paymentID = Null) {
        $this->paymentID = $paymentID;
        $this->processor = new StripeProcessor();
    }

    public function setReturnURL(string $returnURL, string $cancelURL, ?string $failedURL): void
    {
        $this->returnURL = $returnURL;
        $this->cancelURL = $cancelURL;
        $this->failedURL = $failedURL;
    }

    public function initiatePayment($item, ?string $receiptEmail = Null): array
    {
        $paymentRequestData = new Order($this->returnURL, $this->cancelURL);
        $paymentRequestData = $paymentRequestData->payment($item);

        if (!is_null($receiptEmail)) {
            $paymentRequestData['customer_email'] = $receiptEmail;
        }
        $paymentRequestData['metadata'] = [
            'payment_id' => $this->paymentID
        ];

        $transaction = new PayrexxTransaction([
            'payment_id' => $this->paymentID,
            'payload' => json_encode($paymentRequestData)
        ]);

        try {
            $response = $this->processor->createSession($paymentRequestData);
        } catch (\Exception $e) {
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            throw $e;
        }

        try {
            $transaction->response = json_encode($response);
        } catch (\Exception $e) {
            $transaction->response = Null;
        }

        $responseID = $response['id'] ?? Null;

        $transaction->status = PayrexxTransaction::TO_CREATED;
        $transaction->session_id = $responseID;
        $transaction->save();
        Log::channel('payments')->debug("Payment is now created");

        return $response;
    }

    public function setPaymentID(int $paymentID)
    {
        $this->paymentID = $paymentID;
    }

    public function confirmRedirect(array $data): bool
    {
        # Check if the payment is already confirmed
        if (PayrexxTransaction::where('payment_id', $this->paymentID)
                ->where('status', PayrexxTransaction::TO_RETURNED)->exists() === True) {
            return False;
        }

        PayrexxTransaction::create([
            'payment_id' => $this->paymentID,
            'status' => PayrexxTransaction::TO_RETURNED,
            'response' => json_encode($data)
        ]);

        return True;
    }

    public function confirmPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new StripeException('Payment ID is not initialized');
        }

        $transaction = new PayrexxTransaction([
            'payment_id' => $this->paymentID
        ]);

        $transactionID = $this->getTransactionID();
        if(is_null($transactionID)) {
            throw new StripeException('Transaction ID is not found');
        }

        try {
            $sessionDetails = $this->processor->getSession($transactionID);
        } catch (\Exception $e) {
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            throw $e;
        }

        $transaction->response = json_encode($sessionDetails);

        if (array_key_exists('status', $sessionDetails)) {
            if ($sessionDetails['status'] === 'complete') {
                $transaction->status = PayrexxTransaction::TO_COMPLETE;
                $transaction->save();

                if ($sessionDetails['payment_intent'] ?? Null) {
                    $transaction->payment_intent_id = $sessionDetails['payment_intent'];
                    $transaction->save();
                }
                return True;

            } else {
                Log::channel('payments')->error("Capture status is not completed", ['response' => $sessionDetails]);
                $transaction->status = PayrexxTransaction::TO_FAILED;
                $transaction->save();
                return False;
            }
        } else {
            Log::channel('payments')->error("Invalid response content", ['response' => $sessionDetails]);
            $transaction->status = PayrexxTransaction::TO_FAILED;
            $transaction->save();
            return False;
        }

    }

    public function getTransactionID(): mixed
    {
        $creationTransactionRecord = PayrexxTransaction::where('payment_id', $this->paymentID)
            ->where('status', PayrexxTransaction::TO_CREATED)->first();

        if ($creationTransactionRecord) {
            return $creationTransactionRecord->session_id;
        }
    }

    public function getApproveRedirectURL(?int $paymentID): mixed
    {
        if (is_null($paymentID)) {
            $paymentID = $this->paymentID;
        }

        $creationTransactionRecord = PayrexxTransaction::where('payment_id', $paymentID)
            ->where('status', PayrexxTransaction::TO_CREATED)->first();

        if ($creationTransactionRecord) {
            $createResponseRecord = $creationTransactionRecord->response;

            if (array_key_exists('url', $createResponseRecord)) {
                return $createResponseRecord['url'];
            }
        }
        return Null;
    }

    public function cancelPayment(): bool
    {
        if (is_null($this->paymentID)) {
            throw new \Exception('Payment ID is not initialized');
        }

        Log::channel('payments')->info("Cancelling payment", ['id' => $this->paymentID]);

        $expireReturn = $this->processor->expireSession($this->getTransactionID());

        if (array_key_exists('status', $expireReturn) && $expireReturn['status'] === 'expired') {
            Log::channel('payments')->debug("Stripe session expired", ['paymentID' => $this->paymentID]);
            PayrexxTransaction::create([
                'payment_id' => $this->paymentID,
                'status' => PayrexxTransaction::TO_CANCELLED,
                'response' => json_encode(['message' => 'cancelled by request']),
            ]);
            return True;
        } else {
            return False;
        }
    }
}
