<?php

namespace LaravelCommon\App\Services\Payment;

use LaravelCommon\App\Exceptions\PaymentGatewayException;
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

    public function buildTransactionPayload(array $transactionData): array
    {
        $orderId = $transactionData['order_id'] ?? null;
        $amount = $transactionData['amount'] ?? null;

        if (!is_string($orderId) || $orderId === '') {
            throw new PaymentGatewayException('Transaction data must contain a valid order_id');
        }

        if (!is_int($amount) && !is_float($amount)) {
            throw new PaymentGatewayException('Transaction data must contain a valid amount');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
        ];

        $itemDetails = $this->buildItemDetails($transactionData['items'] ?? []);
        if ($itemDetails !== []) {
            $payload['item_details'] = $itemDetails;
        }

        $customerDetails = $this->buildCustomerDetails($transactionData['customer'] ?? []);
        if ($customerDetails !== []) {
            $payload['customer_details'] = $customerDetails;
        }

        $callbacks = $this->buildCallbacks($transactionData['callbacks'] ?? []);
        if ($callbacks !== []) {
            $payload['callbacks'] = $callbacks;
        }

        $enabledPayments = $this->normalizeEnabledPayments($transactionData['enabled_payments'] ?? []);
        if ($enabledPayments !== []) {
            $payload['enabled_payments'] = $enabledPayments;
        }

        $expiry = $this->buildExpiry($transactionData['expiry'] ?? []);
        if ($expiry !== []) {
            $payload['expiry'] = $expiry;
        }

        $customFields = $this->buildCustomFields($transactionData['metadata'] ?? []);
        if ($customFields !== []) {
            $payload = array_merge($payload, $customFields);
        }

        return $payload;
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

    protected function buildItemDetails(mixed $items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $itemDetails = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = $item['name'] ?? null;
            $price = $item['price'] ?? null;

            if (!is_string($name) || $name === '') {
                continue;
            }

            if (!is_int($price) && !is_float($price)) {
                continue;
            }

            $itemDetails[] = array_filter([
                'id' => isset($item['id']) ? (string) $item['id'] : null,
                'price' => $price,
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'name' => $name,
                'brand' => isset($item['brand']) ? (string) $item['brand'] : null,
                'category' => isset($item['category']) ? (string) $item['category'] : null,
                'merchant_name' => isset($item['merchant_name']) ? (string) $item['merchant_name'] : null,
            ], fn ($value) => $value !== null);
        }

        return $itemDetails;
    }

    protected function buildCustomerDetails(mixed $customer): array
    {
        if (!is_array($customer)) {
            return [];
        }

        return array_filter([
            'first_name' => isset($customer['first_name']) ? (string) $customer['first_name'] : null,
            'last_name' => isset($customer['last_name']) ? (string) $customer['last_name'] : null,
            'email' => isset($customer['email']) ? (string) $customer['email'] : null,
            'phone' => isset($customer['phone']) ? (string) $customer['phone'] : null,
            'billing_address' => $this->buildAddress($customer['billing_address'] ?? null),
            'shipping_address' => $this->buildAddress($customer['shipping_address'] ?? null),
        ], fn ($value) => $value !== null && $value !== []);
    }

    protected function buildAddress(mixed $address): array
    {
        if (!is_array($address)) {
            return [];
        }

        return array_filter([
            'first_name' => isset($address['first_name']) ? (string) $address['first_name'] : null,
            'last_name' => isset($address['last_name']) ? (string) $address['last_name'] : null,
            'email' => isset($address['email']) ? (string) $address['email'] : null,
            'phone' => isset($address['phone']) ? (string) $address['phone'] : null,
            'address' => isset($address['address']) ? (string) $address['address'] : null,
            'city' => isset($address['city']) ? (string) $address['city'] : null,
            'postal_code' => isset($address['postal_code']) ? (string) $address['postal_code'] : null,
            'country_code' => isset($address['country_code']) ? (string) $address['country_code'] : null,
        ], fn ($value) => $value !== null);
    }

    protected function buildCallbacks(mixed $callbacks): array
    {
        if (!is_array($callbacks)) {
            return [];
        }

        return array_filter([
            'finish' => isset($callbacks['finish']) ? (string) $callbacks['finish'] : null,
            'error' => isset($callbacks['error']) ? (string) $callbacks['error'] : null,
            'pending' => isset($callbacks['pending']) ? (string) $callbacks['pending'] : null,
        ], fn ($value) => $value !== null);
    }

    protected function normalizeEnabledPayments(mixed $enabledPayments): array
    {
        if (!is_array($enabledPayments)) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                fn ($paymentMethod) => is_string($paymentMethod) && $paymentMethod !== ''
                    ? $paymentMethod
                    : null,
                $enabledPayments
            )
        ));
    }

    protected function buildExpiry(mixed $expiry): array
    {
        if (!is_array($expiry)) {
            return [];
        }

        $duration = $expiry['duration'] ?? null;

        return array_filter([
            'start_time' => isset($expiry['start_time']) ? (string) $expiry['start_time'] : null,
            'unit' => isset($expiry['unit']) ? (string) $expiry['unit'] : null,
            'duration' => is_int($duration) ? $duration : null,
        ], fn ($value) => $value !== null);
    }

    protected function buildCustomFields(mixed $metadata): array
    {
        if (!is_array($metadata) || $metadata === []) {
            return [];
        }

        $metadataValues = array_values($metadata);
        $customFields = [];

        foreach ([1, 2, 3] as $index) {
            $value = $metadataValues[$index - 1] ?? null;

            if ($value === null) {
                continue;
            }

            $customFields["custom_field$index"] = is_scalar($value)
                ? (string) $value
                : json_encode($value);
        }

        return $customFields;
    }
}
