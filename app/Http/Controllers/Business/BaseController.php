<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Traits\ActiveBusiness;

class BaseController extends Controller
{
    use ActiveBusiness;

    /**
     * Check if the office user has access to $business
     *
     * @param int|\App\Business $business   A business model or a business id
     * @return mixed
     */
    public function canAccessBusiness($business)
    {
        return auth()->user()->belongsToBusiness($business);
    }
}
