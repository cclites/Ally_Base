<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * @var \App\Business
     */
    private $business;

    /**
     * @return \App\Business
     * @throws \Exception
     */
    protected function business()
    {
        if (!$this->business) {
            if ( ! $this->business = auth()->user()->role->businesses->first()) {
                throw new \Exception('No default business found.');
            }
        }
        return $this->business;
    }

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
