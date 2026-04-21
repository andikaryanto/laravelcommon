<?php

namespace LaravelCommon\App\Services;

use Illuminate\Contracts\Container\Container;
use LaravelCommon\App\Contracts\PaymentGatewayInterface;
use LaravelCommon\App\Exceptions\PaymentGatewayException;

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
        protected Container $container
    ) {
    }

    public function driver(?string $gateway = null): PaymentGatewayInterface
    {
        $gatewayName = $gateway ?: $this->getDefaultGateway();
        $gatewayConfig = $this->getGatewayConfig($gatewayName);
        $driverClass = $gatewayConfig['driver'] ?? null;

        if (!is_string($driverClass) || $driverClass === '') {
            throw new PaymentGatewayException(
                sprintf("Payment gateway driver for '%s' is not configured", $gatewayName)
            );
        }

        $driver = $this->container->make($driverClass, [
            'config' => $gatewayConfig,
        ]);

        if (!$driver instanceof PaymentGatewayInterface) {
            throw new PaymentGatewayException(
                sprintf(
                    "Payment gateway driver '%s' must implement %s",
                    $driverClass,
                    PaymentGatewayInterface::class
                )
            );
        }

        return $driver;
    }

    public function createTransaction(array $payload, ?string $gateway = null): array
    {
        return $this->driver($gateway)->createTransaction($payload);
    }

    public function buildTransactionPayload(array $transactionData, ?string $gateway = null): array
    {
        return $this->driver($gateway)->buildTransactionPayload($transactionData);
    }

    public function createTransactionFromData(array $transactionData, ?string $gateway = null): array
    {
        return $this->createTransaction(
            $this->buildTransactionPayload($transactionData, $gateway),
            $gateway
        );
    }

    public function getTransactionStatus(string $transactionId, ?string $gateway = null): array
    {
        return $this->driver($gateway)->getTransactionStatus($transactionId);
    }

    public function cancelTransaction(string $transactionId, ?string $gateway = null): array
    {
        return $this->driver($gateway)->cancelTransaction($transactionId);
    }

    public function getDefaultGateway(): string
    {
        $gateway = config('common-config.payment.default_gateway');

        if (!is_string($gateway) || $gateway === '') {
            throw new PaymentGatewayException('Default payment gateway is not configured');
        }

        return $gateway;
    }

    protected function getGatewayConfig(string $gateway): array
    {
        $gatewayConfig = config("common-config.payment.gateways.$gateway");

        if (!is_array($gatewayConfig)) {
            throw new PaymentGatewayException(
                sprintf("Payment gateway '%s' is not registered", $gateway)
            );
        }

        return $gatewayConfig;
    }
}
