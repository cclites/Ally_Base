<?php
return [
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
];
