<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Traits\ActiveBusiness;

class BaseController extends Controller
{
    use ActiveBusiness;

    protected function hasCaregiver($id)
    {
        return $this->business()->caregivers()->where('caregivers.id', $id)->exists();
    }

    /**
     * @return string
     */
    protected function timezone()
    {
        return $this->business()->timezone;
    }

}
