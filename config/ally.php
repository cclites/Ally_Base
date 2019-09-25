<?php
return [
    /**
     * The applications default local timezone for displaying
     * dates on the front-end.
     */
    'local_timezone' => env('LOCAL_TIMEZONE', 'America/New_York'),

    'medicaid_fee' => '0.03',
    'bank_account_fee' => '0.03',
    'credit_card_fee' => '0.05',
    'amex_card_fee' => '0.06',

    /**
     * Flag to turn on/off the functionality of PreventDuplicatePosts
     * middleware for the purposes of development.  The default behavior
     * is to use it.
     */
    'prevent_dupe_posts' => env('PREVENT_DUPE_POSTS', true),

    /**
     * This is the email where results from certain CRON operations
     * will be sent after execution.  If this value is set to blank
     * it will turn off this feature.
     */
    'cron_results_to' => env('CRON_RESULTS_TO', 'jason@allyms.com'),

    /**
     * Flag to turn on/off logging of all raw outgoing email
     * and SMS data.
     */
    'communication_log' => env('COMMUNICATION_LOG', false),
];
