<?php
namespace App\Traits;

trait ActiveBusiness
{
    /**
     * @var \App\Business
     */
    protected $business;

    /**
     * @return \App\Business
     * @throws \Exception
     */
    protected function business()
    {
        if (auth()->check() && auth()->user()->role_type === 'office_user') {
            if (!$this->business) {
                if ( ! $this->business = auth()->user()->role->businesses->first()) {
                    throw new \Exception('No default business found.');
                }
            }
        }
        return $this->business;
    }
}