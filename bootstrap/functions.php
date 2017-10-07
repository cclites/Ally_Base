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

function filter_date_and_time($input_date, $input_time, $input_utc_offset, $output_date_format='Y-m-d', $output_time_format='H:i:s', $output_utc_offset='+00:00') {
    $datetime = new DateTime($input_date . ' ' . $input_time, new DateTimeZone($input_utc_offset));
    $datetime->setTimezone(new DateTimeZone($output_utc_offset));
    $date = $datetime->format($output_date_format);
    $time = $datetime->format($output_time_format);
    return [$date, $time];
}

function filter_datetime($input_datetime, $input_utc_offset, $output_format='Y-m-d H:i:s', $output_utc_offset='+00:00') {
    $date_and_time = filter_date_and_time($input_datetime, '', $input_utc_offset, $output_format, 'H:i:s', $output_utc_offset);
    return $date_and_time[0];
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
