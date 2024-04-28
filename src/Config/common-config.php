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
];
