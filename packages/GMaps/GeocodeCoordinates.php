<?php

namespace Packages\GMaps;

class GeocodeCoordinates
{
    public $latitude;
    public $longitude;

    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param string $units  Units can be km (kilometers), mi (miles), or m (meters)
     *
     * @return float|false
     */
    public function distanceTo($latitude, $longitude, $units='km')
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        $theta = $this->longitude - $longitude;
        $dist = sin(deg2rad($this->latitude)) * sin(deg2rad($latitude))
                +  cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $kilometers = bcmul($dist, "111.18957696", 5);

        switch($units) {
            case 'km':
                return (float) $kilometers;
            case 'mi':
                return (float) bcdiv($kilometers, "1.609344", 5);
            case 'm':
                return (float) bcmul($kilometers, "1000", 2);
        }

        return false;
    }
}
