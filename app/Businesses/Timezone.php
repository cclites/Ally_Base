<?php
/**
 * Created by PhpStorm.
 * User: jamessweeney
 * Date: 3/2/18
 * Time: 12:41 PM
 */

namespace App\Businesses;

use App\Business;
use Illuminate\Support\Facades\Cache;

class Timezone
{
    public static function getTimezone($business_id)
    {
        $timezone = Cache::remember($business_id . '_timezone', 3600, function () use ($business_id) {
            $business = Business::find($business_id);
            return optional($business)->timezone;
        });
        return $timezone;
    }
}