<?php

return [
    'payments' => [
        'manual_bank_transfer' => [
            'bank_name' => env('EPICHUB_BANK_NAME', 'BCA'),
            'account_number' => env('EPICHUB_BANK_ACCOUNT_NUMBER', '1234567890'),
            'account_name' => env('EPICHUB_BANK_ACCOUNT_NAME', 'EPIC HUB'),
        ],
    ],
    'oms' => [
        'enabled' => (bool) env('OMS_INTEGRATION_ENABLED', false),
        'inbound_secret' => (string) env('OMS_INBOUND_SECRET', ''),
        'signature_secret' => (string) env('OMS_SIGNATURE_SECRET', ''),
        'signature_max_skew_seconds' => (int) env('OMS_SIGNATURE_MAX_SKEW_SECONDS', 300),
        'password_encryption_key' => (string) env('OMS_PASSWORD_ENCRYPTION_KEY', ''),
        'outbound_change_password_url' => (string) env('OMS_OUTBOUND_CHANGE_PASSWORD_URL', ''),
        'outbound_timeout' => (int) env('OMS_OUTBOUND_TIMEOUT', 10),
        'response' => [
            'success' => (string) env('OMS_RESPONSE_SUCCESS_CODE', '00'),
            'failed' => (string) env('OMS_RESPONSE_FAILED_CODE', '99'),
        ],
    ],
];

