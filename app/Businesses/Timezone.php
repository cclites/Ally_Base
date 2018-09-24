<?php
namespace App\Businesses;

use App\Business;
use Illuminate\Support\Facades\Cache;

class Timezone
{
    public static function getTimezone($business_id)
    {
        return app('settings')->get($business_id, 'timezone');
    }
}
