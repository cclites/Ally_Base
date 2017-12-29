<?php

namespace App;

use App\GMaps\Geocode;
use App\GMaps\GeocodeCoordinates;
use App\GMaps\NoGeocodeFoundException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Address
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $zip
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $county
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereZip($value)
 * @mixin \Eloquent
 */
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
