<?php

namespace App;

use App\GMaps\Geocode;
use App\GMaps\GeocodeCoordinates;
use App\GMaps\NoGeocodeFoundException;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Get the geocode for the address
     *
     * @return \App\GMaps\GeocodeCoordinates|false
     */
    public function getGeocode($forceUpdate = false)
    {
        if ($forceUpdate || (!$this->latitude && !$this->longitude)) {
            $fullAddress = $this->address1 . ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;
            try {
                $geocode = Geocode::getCoordinates($fullAddress);
                $this->update([
                    'latitude' => $geocode->latitude,
                    'longitude' => $geocode->longitude,
                ]);
            }
            catch (\Exception $e) {
                return false;
            }
        }
        else {
            $geocode = new GeocodeCoordinates($this->latitude, $this->longitude);
        }
        return $geocode;
    }

    /**
     * Calculate the distance between this address and another geocode.
     *
     * @param $latitude
     * @param $longitude
     * @param string $units
     * @return false|float
     */
    public function distanceTo($latitude, $longitude, $units = 'm')
    {
        $geocode = $this->getGeocode();
        return $geocode->distanceTo($latitude, $longitude, $units);
    }

    /**
     * Calculate the distance between this address and another address.
     *
     * @param \App\Address $address
     * @param string $units
     * @return false|float
     */
    public function distanceToAddress(Address $address, $units = 'm')
    {
        $geocode = $address->getGeocode();
        return $this->distanceTo($geocode->latitude, $geocode->longitude, $units);
    }
}
