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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \App\GMaps\GeocodeCoordinates|false
     */
    public function getGeocode($forceUpdate = false)
    {
        if ($this->forceUpdate || (!$this->latitude && !$this->longitude)) {
            $fullAddress = $this->address1 . ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;
            if (!$geocode = Geocode::getCoordinates($fullAddress)) {
                return false;
            }
            $this->update([
                'latitude' => $geocode->latitude,
                'longitude' => $geocode->longitude,
            ]);
        }
        else {
            $geocode = new GeocodeCoordinates($this->latitude, $this->longitude);
        }
        return $geocode;
    }

    public function distanceTo($latitude, $longitude, $units = 'm')
    {
        $geocode = $this->getGeocode();
        return $geocode->distanceTo($latitude, $longitude, $units);
    }
}
