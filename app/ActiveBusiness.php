<?php
namespace App;

use Auth;
use Session;

class ActiveBusiness
{
    protected $business = null;

    public function set(Business $business)
    {
        $this->business = $business;
        Session::put('active_business_id', $business->id);
    }

    public function get()
    {
        if ($this->business) {
            return $this->business;
        }
        if (Auth::check() && Auth::user()->role_type === 'office_user') {
            return Auth::user()->role->businesses->first();
        }
        // For administrators only: get the last used business_id
        // This can be extended to office users once we allow for business switching.
        if ($business_id = Session::get('active_business_id')) {
            return Business::find($business_id);
        }
        // For administrators only: return a blank business to prevent exceptions
        if (is_admin_now()) return new Business();
    }
}
