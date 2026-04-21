<?php

namespace LaravelCommon\App\Contracts;

interface PaymentGatewayInterface
{
    public function createTransaction(array $payload): array;

    public function getTransactionStatus(string $transactionId): array;

    public function cancelTransaction(string $transactionId): array;
}
