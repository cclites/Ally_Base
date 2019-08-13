<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Driver
    |--------------------------------------------------------------------------
    | This option controls the default driver to use for sending SMS messages.
    | It will default to a simple log driver that will log SMS message details
    | to the laravel.log file.
    |
    | Supported: "log", "twilio"
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    /*
     |--------------------------------------------------------------------------
     | Reply Threshold
     |--------------------------------------------------------------------------
     |
     | Holds the global value of the amount of time (in minutes) that
     | must pass from when an sms message is sent until it no 
     | longer will receive responses.
     |
     */

    'reply_threshold' => env('SMS_REPLY_THRESHOLD', 120),
];
