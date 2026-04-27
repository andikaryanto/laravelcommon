<?php

namespace LaravelCommon\App\Services\Payment;

use LaravelCommon\App\Contracts\PaymentGatewayInterface;
use LaravelCommon\App\Exceptions\PaymentGatewayException;

class NullPaymentGateway implements PaymentGatewayInterface
{
    public function buildTransactionPayload(array $transactionData): array
    {
        throw new PaymentGatewayException('Payment gateway is disabled');
    }

    public function createTransaction(array $payload): array
    {
        throw new PaymentGatewayException('Payment gateway is disabled');
    }

    public function getTransactionStatus(string $transactionId): array
    {
        throw new PaymentGatewayException('Payment gateway is disabled');
    }

    public function cancelTransaction(string $transactionId): array
    {
        throw new PaymentGatewayException('Payment gateway is disabled');
    }
}
