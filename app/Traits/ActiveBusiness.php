<?php
namespace App\Traits;

trait ActiveBusiness
{
    /**
     * @return \App\Business
     * @throws \Exception
     */
    protected function business()
    {
        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        if (!$business = $activeBusiness->get()) {
            throw new \Exception('No default business found.');
        }
        return $business;
    }
}