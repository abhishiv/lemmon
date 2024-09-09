<?php
/**
 * Payment Settings
 */
return [
    'paypal' => [
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'base_url' => env("PAYPAL_BASE_URL", "https://api-m.sandbox.paypal.com/")
    ],
    'stripe' => [
        'token' => env('STRIPE_API_TOKEN'),
        'base_url' => env("STRIPE_BASE_URL", "https://api.stripe.com/")
    ],
    'payrexx' => [
        'instance_name' => env('PAYREXX_INSTANCE_NAME'),
        'api_secret' => env('PAYREXX_API_SECRET'),
    ]
];
