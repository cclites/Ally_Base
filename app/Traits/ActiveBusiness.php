<?php
namespace App\Traits;

use App\Caregiver;
use App\Client;

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

    /**
     * Return true if a business has access to the specified client or client id
     *
     * @param int|\App\Client $client
     * @return bool
     */
    protected function businessHasClient($client)
    {
        if ($client instanceof Client) {
            return $client->business_id == $this->business()->id;
        }
        return $this->business()->clients()->where('id', $client)->exists();
    }

    /**
     * Return true if a business has access to the specified caregiver or caregiver id
     *
     * @param $caregiver
     * @return bool
     */
    protected function businessHasCaregiver($caregiver)
    {
        if ($caregiver instanceof Caregiver) {
            $caregiver = $caregiver->id;
        }
        return $this->business()->caregivers()->where('caregiver_id', $caregiver)->exists();
    }
}