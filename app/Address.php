<?php

namespace App;

use App\Services\GeocodeManager;
use App\Traits\ScrubsForSeeding;
use Packages\GMaps\Geocode;
use Packages\GMaps\GeocodeCoordinates;
use Packages\GMaps\NoGeocodeFoundException;
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read string $full_address
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
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
class Address extends AuditableModel
{
    use ScrubsForSeeding;

    protected $table = 'addresses';
    protected $appends = ['full_address'];
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
     * @return \Packages\GMaps\GeocodeCoordinates|false
     */
    public function getGeocode($forceUpdate = false)
    {
        if ($forceUpdate || (!$this->latitude && !$this->longitude)) {
            $fullAddress = $this->address1 . ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;

            try {
                $manager = app(GeocodeManager::class);
                $geocode = $manager->getCoordinates($fullAddress);
                if ($geocode) {
                    $this->update([
                        'latitude' => $geocode->latitude,
                        'longitude' => $geocode->longitude,
                    ]);
                }
            }
            catch (\Packages\GMaps\Exceptions\NoGeocodeFoundException $e) {
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
        if (!$geocode) return false;
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

    /**
     * Get full address string
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        $fullAddress = $this->address1;

        if (!empty($this->address2)) {
            $fullAddress .= ' ' . $this->address2;
        }


        $fullAddress .= ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;

        return $fullAddress;
    }

    /**
     * @return string|null
     */
    public function getStreetAddressAttribute()
    {
        $fullAddress = $this->address1;

        if (!empty($this->address2)) {
            $fullAddress .= ' ' . $this->address2;
        }

        return $fullAddress;
    }

    /**
     * @return string
     */
    public function getCityStateZipAttribute(){
        return $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'address1' => $faker->streetAddress,
            'latitude' => $faker->latitude,
            'longitude' => $faker->longitude,
        ];
    }
}
