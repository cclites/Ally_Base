<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'ecs' => [
        'username' => env('ECS_PAYMENTS_USERNAME'),
        'password' => env('ECS_PAYMENTS_PASSWORD')
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_APP_TOKEN'),
        'default_number' => env('TWILIO_DEFAULT_NUMBER'),
    ],

    'twilio-sandbox' => [
        'sid' => env('TWILIO_SANDBOX_SID'),
        'token' => env('TWILIO_SANDBOX_APP_TOKEN'),
        'default_number' => env('TWILIO_SANDBOX_DEFAULT_NUMBER'),
    ],

    'slack' => [
        'endpoint' => env('SLACK_ENDPOINT'),
        'channel' => env('SLACK_CHANNEL')
    ],

    'gmaps' => [
        'key' => env('GMAPS_API_KEY'),
    ],

    'microbilt' => [
        'id' => env('MICROBILT_ID'),
        'password' => env('MICROBILT_PASSWORD'),
    ],

    'tellus' => [
        'endpoint' => env('TELLUS_ENDPOINT', 'https://integration.pilot.4tellus.com/v1.0/ALLY'),
    ],

    'fullcalendar' => [
        'key' => env('FULLCALENDAR_KEY'),
    ],

    'sftp' => [
        'driver' => env('SFTP_DRIVER', 'dummy'),
    ],

    'hha-exchange' => [
        'sftp_host' => env('HHAEXCHANGE_SFTP_HOST', ''),
        'sftp_port' => env('HHAEXCHANGE_SFTP_PORT', 22),
        'sftp_directory' => env('HHAEXCHANGE_SFTP_DIRECTORY', '/'),
    ],

    'quickbooks' => [
        'client_id' => env('QUICKBOOKS_APP_CLIENT_ID', ''),
        'client_secret' => env('QUICKBOOKS_APP_CLIENT_SECRET', ''),
        /**
         * env mode, options: production, sandbox
         * defaults to 'sandbox'
         */
        'mode' => env('QUICKBOOKS_MODE', 'sandbox'),
    ],
];
