<?php
namespace App\Payment\Processors;

use App\Models\Order;

interface PaymentServiceInterface {

    public function setPaymentID(int $paymentID);

    public function setReturnURL(string $returnURL, string $cancelURL, ?string $failedUrl);

    public function initiatePayment(Order $order): array;

    public function confirmRedirect(array $data): bool;

    public function confirmPayment(): bool;

    public function getTransactionID(): mixed;

    public function getApproveRedirectURL(?int $paymentID): mixed;

    public function cancelPayment(): bool;

}
