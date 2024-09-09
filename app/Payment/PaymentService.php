<?php

namespace App\Payment;

use App\Payment\Exceptions\PaypalException;
use App\Payment\Exceptions\PayrexxException;
use App\Payment\Exceptions\StripeException;
use App\Payment\Processors\PaymentServiceInterface;
use Exception;
use App\Payment\Exceptions\PaymentException;
use App\Payment\Models\Payment;
use App\Models\Order;
use App\Payment\Processors\Paypal\PaypalPaymentService;
use App\Payment\Processors\Stripe\StripePaymentService;
use App\Payment\Processors\Payrexx\PayrexxPaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaymentService
{

    private PaymentServiceInterface $paymentProcessorService;
    private ?Payment $payment;

    /**
     * Create the Payment Service
     * @param string $paymentType
     * @param mixed $paymentID
     * @throws Exception
     */
    public function __construct(string $paymentType, mixed $paymentID = null)
    {
        $this->paymentProcessorService = match ($paymentType) {
            PaypalPaymentService::NAME => new PaypalPaymentService($paymentID),
            StripePaymentService::NAME => new StripePaymentService($paymentID),
            PayrexxPaymentService::NAME => new PayrexxPaymentService($paymentID),
            default => throw new Exception('Unknown payment type'),
        };

        $paymentID ? $this->payment = Payment::find($paymentID) : $this->payment = null;
    }

    /**
     * Set the return URL for the service
     *
     * @param string $returnURL
     * @param string $cancelURL
     * @param string $failedURL
     */
    public function setReturnURL(string $returnURL, string $cancelURL, string $failedURL): void
    {
        $this->paymentProcessorService->setReturnURL($returnURL, $cancelURL, $failedURL);
    }


    /**
     * Check if the ID exists in payments
     *
     * @param $paymentID
     * @return bool
     */
    public static function hasPayment($paymentID): bool
    {
        return Payment::where('uuid', $paymentID)->exists();
    }

    /**
     * Get a payment record from ID
     *
     * @param mixed $paymentID
     * @return Payment|null
     */
    private static function fetchPaymentRecord(mixed $paymentID): ?Payment
    {
        if (!is_null($paymentID)) {
            return Payment::where('uuid', $paymentID)->first();
        }
        return null;
    }

    /**
     * Get a payment service from ID
     *
     * @param mixed $UUID
     * @param string|null $provider
     * @return static|null
     * @throws PaymentException
     */
    public static function getByUUID(mixed $UUID, ?string $provider = null): ?self
    {
        $paymentRecord = Payment::where('uuid', $UUID);

        if (!is_null($provider)) {
            $paymentRecord->where('provider', Payment::getProvider($provider));
        }
        $paymentRecord = $paymentRecord->first();

        return null;
    }

    /**
     * Get a payment service from transaction ID
     *
     * @param mixed $transactionID
     * @param string|null $provider
     * @return static|null
     * @throws PaymentException|Exception
     */
    public static function getByTransactionID(mixed $transactionID, ?string $provider = null): ?self
    {
        $paymentRecord = Payment::where('transaction_code', $transactionID);

        if (!is_null($provider)) {
            $paymentRecord->where('provider', Payment::getProvider($provider));
        }
        $paymentRecord = $paymentRecord->first();

        return null;
    }

    /**
     * Cancel a payment to provider
     *
     * @return bool
     * @throws Exception
     */
    public function cancelPayment(): bool
    {
        if ($this->payment->status === Payment::PENDING || $this->payment->status === Payment::INITIAL) {
            $cancelResult = $this->paymentProcessorService->cancelPayment();

            if ($cancelResult === true) {
                Log::channel('payments')->info("Payment cancelled", ['uuid' => $this->getUUID()]);
                $this->payment->update(['status' => Payment::CANCELLED]);
                return true;
            }
        }
        return false;
    }

    /**
     * Create a new payment record
     *
     * @param Order $order
     * @return void
     */
    private function create(Order $order): void
    {
        $this->payment = Payment::create([
            'uuid'      => self::generateUUID(),
            'amount'    => $order->totalAmount,
            'order_id'  => $order->id,
            'provider'  => Payment::getProvider($this->paymentProcessorService::NAME),
            'status'    => Payment::INITIAL,
        ]);

        $this->setReturnURL(
            route('customer.payment.success', $this->payment->id),
            route('customer.payment.cancel', $this->payment->id),
            route('customer.payment.failed', $this->payment->id)
        );

        $this->paymentProcessorService->setPaymentID($this->payment->id);
    }

    /**
     * Begin the payment process
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function initiatePayment(Order $order): array
    {
        Log::channel('payments')->info("Starting new payment process",
            ['paymentData' => print_r($order->toArray(), true)]);

        $this->create($order);

        try {
            $paymentData = $this->paymentProcessorService->initiatePayment($order);
        } catch (PaypalException|PayrexxException|StripeException $e) {
            $this->payment->update(['status' => Payment::CANCELLED]);
            throw $e;
        }

        $transaction_code = $this->paymentProcessorService->getTransactionID();

        Log::channel('payments')->info('Payrexx transaction ID (Transaction Token): ' . print_r($transaction_code, true));

        $this->payment->update([
            'status' => Payment::PENDING,
            'transaction_code' => $transaction_code
        ]);

        return $paymentData;
    }

    /**
     * Get redirect URL for payment
     *
     * @return mixed|string|null
     * @throws PayrexxException
     */
    public function getApproveRedirectURL(): mixed
    {
        return $this->paymentProcessorService->getApproveRedirectURL($this->payment->id);
    }

    /**
     * Update the payment transaction after the user is redirected back to return url
     *
     * @param $data
     * @return boolean
     * @throws Exception
     */
    public function confirmRedirect($data): bool
    {
        if ($this->payment->status === Payment::PENDING) {
            $confirmationResult = $this->paymentProcessorService->confirmRedirect($data);

            if ($confirmationResult === true) {
                $this->payment->update(['status' => Payment::APPROVED]);
            }
            return $confirmationResult;
        } else {
            return false;
        }
    }

    /**
     * Confirm a payment to provider
     *
     * @return bool
     * @throws Exception
     */
    public function confirmPayment(): bool
    {
        if ($this->payment->status === Payment::APPROVED) {
            $confirmationResult = $this->paymentProcessorService->confirmPayment();
            $this->payment->update(['status' => $confirmationResult === true ? Payment::CONFIRMED : Payment::ERROR]);
            return $confirmationResult;
        } 
        else if($this->payment->status === Payment::CONFIRMED) { // If payment was already confirmed
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Attempt to confirm a payment that is in PENDING state.
     *
     * @return bool
     * @throws Exception
     */
    public function attemptPaymentConfirmation(): bool
    {
        if ($this->payment->status === Payment::PENDING) {
            $confirmationResult = $this->paymentProcessorService->confirmPayment();

            if ($confirmationResult === true) {
                $this->payment->update(['status' => Payment::CONFIRMED]);
            }
            return $confirmationResult;
        } else {
            return false;
        }
    }

    /**
     * Generate UUID for the payment as an extra validation layer
     *
     * @return string
     */
    public function generateUUID(): string
    {
        $uuid = Str::uuid()->toString();

        if (Payment::where('uuid', $uuid)->doesntExist()) {
            return $uuid;
        }

        return self::generateUUID();
    }

    /**
     * Get UUID of payment
     *
     * @return ?string
     */
    public function getUUID(): ?string
    {
        if ($this->payment) {
            return $this->payment->uuid ?? null;
        }
        return null;
    }
}
