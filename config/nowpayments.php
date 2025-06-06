<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NOWPayments API Configuration
    |--------------------------------------------------------------------------
    |
    | These are the credentials and settings for NOWPayments API integration.
    | You should set these values in your .env file for security.
    |
    */

    'api_key' => env('NOWPAYMENTS_API_KEY', ''),
    'api_url' => env('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io'),
    'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET', ''),
    'sandbox' => env('NOWPAYMENTS_SANDBOX', false),
]; 