<?php
namespace App\Services;

use App\Contracts\GeocodeInterface;
use Packages\GMaps\GeocodeCoordinates;

class DummyGeocodeService implements GeocodeInterface
{

    public function getResult(string $address)
    {
        return [];
    }

    public function getCoordinates(string $address)
    {
        return new GeocodeCoordinates(mt_rand(40,45), mt_rand(-80,-70));
    }

    public function reverseGeocode($latitude, $longitude, $language = 'en')
    {
        return [];
    }
}