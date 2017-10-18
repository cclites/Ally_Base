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

/**
 * Handle input of ISO, output to separate date and time fields (for database entry)
 *
 * @param $iso_input
 * @param null|string $output_utc_offset  Leave null to keep the same timezone as input
 * @param string $output_date_format
 * @param string $output_time_format
 *
 * @return array
 */
function split_date_and_time($iso_input, $output_utc_offset = null, $output_date_format='Y-m-d', $output_time_format='H:i:s') {
    $datetime = \Carbon\Carbon::createFromFormat(DATE_ISO8601, $iso_input);
    if ($output_utc_offset) $datetime->setTimezone(new DateTimeZone($output_utc_offset));
    $date = $datetime->format($output_date_format);
    $time = $datetime->format($output_time_format);
    return [$date, $time];
}

/**
 * Handle input of date and time, output to ISO
 *
 * @param $date
 * @param $time
 * @param string $timezone
 * @param null|string $output_timezone  Leave null to keep the same timezone as $timezone
 * @param string $output_date_format
 * @return string
 */
function api_date_and_time($date, $time, $timezone='UTC', $output_timezone = null, $output_date_format='c') {
    $datetime = new \Carbon\Carbon($date . ' ' . $time, $timezone);
    if ($output_timezone) $datetime->setTimezone(new DateTimeZone($output_timezone));
    return $datetime->format($output_date_format);
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
