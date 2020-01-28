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
        
        if (is_office_user()) {
            if (Auth::user()->active === 0) {
                // This resolves an issue where office users that have
                // been deactivated and are already logged in receive
                // an exception before they are bounced to the login screen.
                return new Business(); // Return a blank business to prevent exceptions
            }

            return Auth::user()->role->getDefaultBusiness();
        }

        // For administrators only: get the last used business_id
        // This can be extended to office users once we allow for business switching.
        if ($business_id = Session::get('active_business_id')) {
            return Business::find($business_id);
        }
        // For administrators only: return a blank business to prevent exceptions
        if (is_admin_now()) {
            return new Business();
        }
    }
}
