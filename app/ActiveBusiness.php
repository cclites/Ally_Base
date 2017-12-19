<?php
namespace App;

class ActiveBusiness
{
    protected $business = null;

    public function set(Business $business)
    {
        $this->business = $business;
    }

    public function get()
    {
        if (auth()->check() && auth()->user()->role_type === 'office_user') {
            if (!$this->business && !$this->business = auth()->user()->role->businesses->first()) {
                return null;
            }
        }
        return $this->business;
    }
}