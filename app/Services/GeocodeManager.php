<?php
namespace App\Services;

use App\Contracts\GeocodeInterface;
use Illuminate\Cache\CacheManager;
use Packages\GMaps\Exceptions\NoGeocodeFoundException;
use Packages\GMaps\Geocode;

class GeocodeManager
{
    private $service;
    private $cacheManager;

    public function __construct(GeocodeInterface $service = null, CacheManager $cacheManager = null)
    {
        $this->service = $service;
        $this->cacheManager = $cacheManager ?? app(CacheManager::class);
    }

    /**
     * Base Geocode Result for an Address
     *
     * @param $address
     * @return array|bool
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
     */
    public function getResult(string $address)
    {
        if ($this->service) {
            return $this->service->getResult($address);
        }

        return Geocode::getResult($address);
    }

    /**
     * @param $address
     * @return \Packages\GMaps\GeocodeCoordinates|bool
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
     */
    public function getCoordinates(string $address)
    {
        if ($this->isAddressMarkedInvalid($address)) {
            throw new NoGeocodeFoundException();
        }

        try {
            if ($this->service) {
                $result = $this->service->getCoordinates($address);
            } else {
                $result = Geocode::getCoordinates($address);
            }
        }
        catch (NoGeocodeFoundException $e) {
            $this->markAddressInvalid($address);
            throw $e;
        }

        if (!$result) {
            $this->markAddressInvalid($address);
        }

        return $result;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param string $language
     * @return mixed
     * @throws \Packages\GMaps\Exceptions\NoGeocodeFoundException
     */
    public function reverseGeocode($latitude, $longitude, $language='en')
    {
        if ($this->service) {
            return $this->service->reverseGeocode($latitude, $longitude, $language);
        }

        return Geocode::reverseGeocode($latitude, $longitude, $language);
    }

    protected function markAddressInvalid(string $address)
    {
        // Store for 5 minutes
        $this->cacheManager->put($this->getInvalidAddressKey($address), 1, 5);
    }

    protected function isAddressMarkedInvalid(string $address)
    {
        return $this->cacheManager->has($this->getInvalidAddressKey($address));
    }

    protected function getInvalidAddressKey(string $address)
    {
        return 'invalid_address_' . md5($address);
    }
}