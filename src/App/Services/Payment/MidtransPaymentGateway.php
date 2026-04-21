<?php

namespace LaravelCommon\App\Services\Payment;

use LaravelCommon\App\Contracts\PaymentGatewayInterface;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected array $config = []
    ) {
    }

    public function createTransaction(array $payload): array
    {
        $this->applyConfiguration();

        return $this->normalizeResponse(Snap::createTransaction($payload));
    }

    public function getTransactionStatus(string $transactionId): array
    {
        $this->applyConfiguration();

        return $this->normalizeResponse(Transaction::status($transactionId));
    }

    public function cancelTransaction(string $transactionId): array
    {
        $this->applyConfiguration();

        return $this->normalizeResponse(Transaction::cancel($transactionId));
    }

    protected function applyConfiguration(): void
    {
        Config::$serverKey = (string) ($this->config['server_key'] ?? '');
        Config::$clientKey = (string) ($this->config['client_key'] ?? '');
        Config::$isProduction = (bool) ($this->config['is_production'] ?? false);
        Config::$isSanitized = (bool) ($this->config['is_sanitized'] ?? true);
        Config::$is3ds = (bool) ($this->config['is_3ds'] ?? true);
    }

    protected function normalizeResponse(mixed $response): array
    {
        if (is_array($response)) {
            return $response;
        }

        $normalized = json_decode(json_encode($response), true);

        return is_array($normalized) ? $normalized : [];
    }
}
