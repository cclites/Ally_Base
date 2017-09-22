<?php

function json_phone(\App\User $user, $type) {
    if ($phoneNumber = $user->phoneNumbers->where('type', $type)->first()) {
        return sprintf('{number: \'%s\', extension: %s}',
            $phoneNumber->numberOnly(),
            $phoneNumber->extension ? (int) $phoneNumber->extension : 'null'
            );
    }
    return '{}';
}

function filter_date($input, $to_format='Y-m-d') {
    if (!$input) return null;
    $carbon = new \Carbon\Carbon($input);
    return $carbon->format($to_format);
}

function todays_date($format = 'Y-m-d') {
    return local_date('now', $format);
}

function local_date($input, $to_format='m/d/Y', $from_timezone='UTC') {
    $carbon = new \Carbon\Carbon($input, $from_timezone);
    $carbon->timezone('America/New_York');
    return $carbon->format($to_format);
}

function utc_date($input, $to_format='Y-m-d H:i:s', $from_timezone='America/New_York') {
    $carbon = new \Carbon\Carbon($input, $from_timezone);
    $carbon->timezone('UTC');
    return $carbon->format($to_format);
}
