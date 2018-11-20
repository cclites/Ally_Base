<?php

/**
 * Space out curly braces to prevent XSS attacks with Vue.js interpolation
 *
 * @param  string  $value
 * @return string
 */
function interpol_escape($value)
{
    return preg_replace('/({|}|&#123;|&#125;|&#x7b;|&#x7d;)(?=\S)/', '$1 ', $value);
}

function is_mobile_app($agent = null)
{
    if (!$agent) $agent = request()->userAgent();
    $match = ' AllyMS Mobile ';
    return strpos($agent, $match) !== false;
}

function collection_only_values($collection, $values = []) {
    return $collection->map(function($item) use ($values)
    {
        return array_intersect_key($item->toArray(), array_flip($values));
    });
}

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

function filter_dates(...$dates) {
    return array_map(function($date) {
        return filter_date($date);
    }, $dates);
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
function api_date_and_time($date, $time, $timezone='UTC', $output_timezone = null, $output_date_format=DATE_ISO8601) {
    $datetime = new \Carbon\Carbon($date . ' ' . $time, $timezone);
    if ($output_timezone) $datetime->setTimezone(new DateTimeZone($output_timezone));
    return $datetime->format($output_date_format);
}

function todays_date($format = 'Y-m-d', $to_timezone = 'America/New_York') {
    return local_date('now', $format, $to_timezone);
}

function local_date($input, $to_format='m/d/Y', $to_timezone = 'America/New_York', $from_timezone='UTC') {
    $carbon = new \Carbon\Carbon($input, $from_timezone);
    $carbon->timezone($to_timezone);
    return $carbon->format($to_format);
}

function utc_date($input, $to_format='Y-m-d H:i:s', $from_timezone='America/New_York') {
    $carbon = new \Carbon\Carbon($input, $from_timezone);
    $carbon->timezone('UTC');
    return $carbon->format($to_format);
}

/**
 * Check if an administrator is logged in or impersonating another user
 *
 * @return bool
 */
function is_admin() {
    if (Auth::check() && $impersonator = Auth::user()->impersonator()) {
        return $impersonator->role_type === 'admin';
    }
    return is_admin_now();
}

/**
 * Check if an administrator is logged in and NOT impersonating another user
 *
 * @return bool
 */
function is_admin_now() {
    return Auth::check() && Auth::user()->role_type === 'admin';
}

/**
 * Check if the logged in user is an office user
 *
 * @return bool
 */
function is_office_user() {
    return Auth::check() && Auth::user()->role_type === 'office_user';
}


if (! function_exists('activeBusiness')) {
    /**
     * Get the active business object or return null.
     *
     * @return string
     * @deprecated
     */
    function activeBusiness() {
        if (Auth::check() && Auth::user()->role_type === 'caregiver') {
            return Auth::user()->role->businesses->first();
        }

        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        if (!$business = $activeBusiness->get()) {
            return null;
        }

        return $business;
    }
}
