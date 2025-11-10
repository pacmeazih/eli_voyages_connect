<?php

return [

    /*
    |--------------------------------------------------------------------------
    | DocuSeal API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for DocuSeal electronic signature integration.
    | Get your API key from: https://console.docuseal.com/api
    |
    */

    'api_key' => env('DOCUSEAL_API_KEY'),
    'api_url' => env('DOCUSEAL_API_URL', 'https://api.docuseal.co'),

    /*
    |--------------------------------------------------------------------------
    | Template IDs
    |--------------------------------------------------------------------------
    |
    | DocuSeal template IDs for different contract types.
    | Create templates at: https://console.docuseal.com/templates
    |
    */

    'templates' => [
        'service' => env('DOCUSEAL_TEMPLATE_SERVICE'),
        'reservation' => env('DOCUSEAL_TEMPLATE_RESERVATION'),
        'payment' => env('DOCUSEAL_TEMPLATE_PAYMENT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook URL in DocuSeal console:
    | https://yourdomain.com/api/webhooks/docuseal
    |
    */

    'webhook_url' => env('APP_URL') . '/api/webhooks/docuseal',

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'send_email' => true,           // Send email notifications to signers
        'require_email' => true,        // Require email verification
        'expire_after_days' => 30,      // Expiration days (0 = never)
        'allow_decline' => true,        // Allow signers to decline
        'completed_redirect_url' => env('APP_URL') . '/contracts/completed',
    ],

];
