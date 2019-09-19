<?php
namespace App\Businesses;

use App\Business;
use Illuminate\Support\Facades\Cache;

class Timezone
{
    /**
     * @param $business_id
     * @return string
     */
    public static function getTimezone($business_id): string
    {
        return (string) app('settings')->get($business_id, 'timezone') ?: config('ally.local_timezone');
    }
}
