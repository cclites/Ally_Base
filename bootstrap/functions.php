<?php

function filter_phone($input) {
    if (!$input) return null;
    return preg_replace("/[^\dx]/", "", $input);
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