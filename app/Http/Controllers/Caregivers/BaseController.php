<?php
namespace App\Http\Controllers\Caregivers;

use App\Businesses\Timezone;
use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    /**
     * Get the authenticated caregiver model
     *
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return \Auth::user()->caregiver;
    }

    /**
     * Return the timezone related to the first relevant business.
     * TODO: Update this logic since multi-business could really cause this to be invalid.  Possibly use the first client instead?
     *
     * @return string
     */
    protected function timezone()
    {
        return Timezone::getTimezone($this->caregiver()->getBusinessIds()[0]) ?? 'America/New_York';
    }

}