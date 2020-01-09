<?php

use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Validation\ValidationException;

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

/**
 * Check to see if the user agent matches the Ally Mobile App
 *
 * @param null $agent
 * @return bool
 */
function is_mobile_app($agent = null)
{
    if (!$agent) $agent = request()->userAgent();
    $match = ' AllyMS Mobile ';
    return strpos($agent, $match) !== false;
}

function is_ios() {
    $userAgent = request()->userAgent();
    $parser = UAParser\Parser::create();
    $parsed = $parser->parse($userAgent);
    if (optional($parsed->os)->family == 'iOS') {
        return true;
    }

    return false;
}

/**
 * Map a polymorphic type in the database to a class name
 *
 * @param string $dbType
 * @return string|null  Class name
 */
function maps_to_class(?string $dbType): ?string
{
    return strval(config("database.polymorphism.${dbType}")) ?: null;
}

/**
 * Map a class name to a polymorphic type in the database
 *
 * @param string $className
 * @return string|null  Polymorphic type mapping
 */
function maps_from_class(string $className): ?string
{
    $array = config("database.polymorphism");
    return strval(array_search($className, $array)) ?: null;
}

/**
 * Map a polymorphic relation in the database to a Model instance
 *
 * @param string $dbType
 * @param $dbId
 * @return \Illuminate\Database\Eloquent\Model|null
 */
function maps_to_model(?string $dbType, $dbId): ?\Illuminate\Database\Eloquent\Model
{
    if ($class = maps_to_class($dbType)) {
        return (new $class)->find($dbId);
    }

    return null;
}

/**
 * Map a Model instance to a polymorphic type in the database
 *
 * @param \Illuminate\Database\Eloquent\Model $model
 * @return string|null  Polymorphic type mapping
 */
function maps_from_model(\Illuminate\Database\Eloquent\Model $model): ?string
{
    $className = get_class($model);
    $array = config("database.polymorphism");
    return strval(array_search($className, $array)) ?: null;
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


////////////////////////////////////
//// Float Safe Math Functions
////////////////////////////////////

function add($operand1, $operand2, $decimals=2): float
{
    return round(
        bcadd($operand1, $operand2, ceil($decimals*2)),
        $decimals
    );
}

function subtract($operand1, $operand2, $decimals=2): float
{
    return round(
        bcsub($operand1, $operand2, ceil($decimals*2)),
        $decimals
    );
}

function divide($operand1, $operand2, $decimals=2): float
{
    return round(
        bcdiv($operand1, $operand2, ceil($decimals*2)),
        $decimals
    );
}

function multiply($operand1, $operand2, $decimals=2): float
{
    return round(
        bcmul($operand1, $operand2, ceil($decimals*2)),
        $decimals
    );
}

/**
 * Displays up to 4 decimal places, with a minimum of 2 decimal places, trimming unnecessary zeroes
 *
 * @param mixed $number
 * @param int $minimumDecimals
 * @param int $maximumDecimals
 * @return string
 */
function rate_format($number, int $minimumDecimals = 2, int $maximumDecimals = 4): string
{
    $formatted = number_format($number, $maximumDecimals, '.', ',');
    $minimumLength = strpos($formatted, '.') + $minimumDecimals + 1;
    $extra = rtrim(substr($formatted, $minimumLength), "0");
    return substr($formatted, 0, $minimumLength) . $extra;
}

////////////////////////////////////
//// Date Functions
////////////////////////////////////

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

/**
 * Check if the logged in user is a client
 *
 * @return bool
 */
function is_client() {
    return Auth::check() && Auth::user()->role_type === 'client';
}

/**
 * Check if the logged in user is a caregiver
 *
 * @return bool
 */
function is_caregiver() {
    return Auth::check() && Auth::user()->role_type === 'caregiver';
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

if (! function_exists('faker')) {
    /**
     * @return \Faker\Generator
     */
    function faker() {
        return $faker = Faker\Factory::create();
    }
}

function view_component(string $component, string $title, array $props = [], array $breadcrumbs = [], string $layout = 'app')
{
    return view('vue', compact('component', 'title', 'props', 'breadcrumbs', 'layout'));
}

if (! function_exists('alterStartOfWeekDay')) {
    /**
     * Alter the day of the week that weeks start on and then
     * revert back to the original week start value after
     * the callback is called.
     *
     * @param int $day
     * @param Closure $callback
     * @return mixed
     */
    function alterStartOfWeekDay(int $day, Closure $callback) {
        $previousWeekStart = Carbon::getWeekStartsAt();
        $previousWeekEnd = Carbon::getWeekEndsAt();

        $week_end = $day == Carbon::SUNDAY ? Carbon::SATURDAY : ((int)$day) - 1;

        Carbon::setWeekStartsAt((int) $day);
        Carbon::setWeekEndsAt($week_end);

        $result = $callback();

        Carbon::setWeekStartsAt($previousWeekStart);
        Carbon::setWeekEndsAt($previousWeekEnd);

        return $result;
    }
}

if (! function_exists('throw_validation_exception')) {
    /**
     * Throw a custom validation exception.
     *
     * @param array $messages
     * @throws ValidationException
     */
    function throw_validation_exception(array $messages) : void
    {
        throw ValidationException::withMessages($messages);
    }
}

if (! function_exists('snake_to_title_case')) {
    /**
     * Convert snake_case to Title Case.
     *
     * @param string $str
     * @return string
     */
    function snake_to_title_case(string $str): string
    {
        return title_case(preg_replace('/_/', ' ', $str));
    }
}

if (! function_exists('download_file')) {
    /**
     * Download file from the given URL and save it to the local storage disk.
     * Usage: download_file('http://path', \Storage::disk('public'), 'path\file.txt')
     *
     * @param string $url
     * @param FilesystemAdapter $disk
     * @param string $filename
     * @return bool
     */
    function download_file(string $url, FilesystemAdapter $disk, string $filename): bool
    {
        try {
            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($process, CURLOPT_FOLLOWLOCATION, true);

            if (! ($result = curl_exec($process))) {
                return false;
            }

            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
            curl_close($process);

            if ($responseCode != "200") {
                return false;
            }

            $contents = substr($result, $header_size);
            $disk->put($filename, $contents);
            return true;

        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return false;
        }
    }
}

if (! function_exists('dump_csv')) {
    function dump_csv(string $filename, \Illuminate\Support\Collection $data, array $headers = null): bool
    {
        if (! count($data)) {
            return false;
        }

        $fp = fopen($filename, 'w');

        if ($headers) {
            fputcsv($fp, $headers);
        } else {
            fputcsv($fp, array_keys($data->first()));
        }

        $data->each(function ($row) use ($fp) {
            fputcsv($fp, $row);
        });

        fclose($fp);

        return true;
    }
}