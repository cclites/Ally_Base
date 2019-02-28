<?php


namespace App\Contracts;


interface GeocodeInterface
{
    public function getResult(string $address);
    public function getCoordinates(string $address);
    public function reverseGeocode($latitude, $longitude, $language='en');

}