<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Contracts\PaymentGatewayInterface;

class PaymentService
{
    /**
     * Standard transaction data shape:
     * - order_id: string
     * - amount: int|float
     * - items?: array<int, array{id?: string, name: string, price: int|float, quantity?: int}>
     * - customer?: array{first_name?: string, last_name?: string, email?: string, phone?: string, billing_address?: array, shipping_address?: array}
     * - callbacks?: array{finish?: string, error?: string, pending?: string}
     * - enabled_payments?: array<int, string>
     * - expiry?: array{start_time?: string, unit?: string, duration?: int}
     * - metadata?: array<string, mixed>
     */
    public function __construct(
        protected PaymentGatewayInterface $paymentGateway
    ) {
    }

    public function createTransaction(array $payload): array
    {
        return $this->paymentGateway->createTransaction($payload);
    }

    public function buildTransactionPayload(array $transactionData): array
    {
        return $this->paymentGateway->buildTransactionPayload($transactionData);
    }

    public function createTransactionFromData(array $transactionData): array
    {
        return $this->createTransaction(
            $this->buildTransactionPayload($transactionData)
        );
    }

    public function getTransactionStatus(string $transactionId): array
    {
        return $this->paymentGateway->getTransactionStatus($transactionId);
    }

    public function cancelTransaction(string $transactionId): array
    {
        return $this->paymentGateway->cancelTransaction($transactionId);
    }
}
