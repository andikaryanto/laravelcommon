<?php

return [
    'api' => [
        'use_resource_key' => true
    ],
    'collection_paging' => [
        'size' => 100
    ],
    'entity' => [
        'mapping' => [
            'app' => base_path() . '/app/Entities/Mapping'
        ]
    ],
    'time_zone' => [
        'use_custom_timezone' => true,
        'time_zone' => 'Asia/Jakarta'
    ],
    'env' => [
        'local' => [
            'rollbar_access_token' => '',
        ],
        'test' => [
            'rollbar_access_token' => '',
        ],
        'staging' => [
            'rollbar_access_token' => '',
        ],
        'production' => [
            'rollbar_access_token' => '',
        ]
        ],
    'jwt' => [
        'expired_in_days' => 90
    ],
    'payment' => [
        'default_gateway' => env('PAYMENT_GATEWAY', 'midtrans'),
        'gateways' => [
            'midtrans' => [
                'driver' => \LaravelCommon\App\Services\Payment\MidtransPaymentGateway::class,
                'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
                'server_key' => env('MIDTRANS_SERVER_KEY', ''),
                'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
                'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
                'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
                'is_3ds' => env('MIDTRANS_IS_3DS', true),
            ],
        ],
    ],
];
