<?php
return [
    /**
     * The applications default local timezone for displaying
     * dates on the front-end.
     */
    'local_timezone' => env('LOCAL_TIMEZONE', 'America/New_York'),

    'bank_account_fee' => '0.03',
    'credit_card_fee' => '0.05',
    'amex_card_fee' => '0.06',
    'trust_fee' => '0.07',
    'gateway' => env('GATEWAY_DRIVER', 'ECS'),

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

    'data_dump' => [
        /**
         * The ID of the confluence document that contains the Ally database dumps.
         */
        'confluence_content_id' => env('DATA_DUMP_CONFLUENCE_CONTENT_ID', ''),

        /**
         * The password for the protected database dump zip files.
         */
        'zip_password' => env('DATA_DUMP_ZIP_PASSWORD', ''),
    ],
];
