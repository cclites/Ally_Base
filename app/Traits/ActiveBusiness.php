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
        if (!$this->business) {
            if ( ! $this->business = auth()->user()->role->businesses->first()) {
                throw new \Exception('No default business found.');
            }
        }
        return $this->business;
    }
}