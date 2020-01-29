<?php

namespace Tests\Unit;

use Packages\GMaps\GeocodeCoordinates;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    /**
     * @var GeocodeCoordinates
     */
    protected $geocode;
    protected $latitude = 43.000;
    protected $longitude = -89.000;

    public function setUp() : void
    {
        parent::setUp();
        $this->geocode = new GeocodeCoordinates($this->latitude, $this->longitude);
    }

    public function testLatitudeAndLogitudeAreAccessible()
    {
        $this->assertEquals($this->latitude, $this->geocode->latitude);
        $this->assertEquals($this->longitude, $this->geocode->longitude);
    }

    public function testLatitudeDistanceCalculation()
    {
        $distance = $this->geocode->distanceTo(44, -89, 'm');
        $this->assertLessThan(111210.0, $distance);
        $this->assertGreaterThan(111170.0, $distance);
    }

    public function testLongitudeDistanceCalculation()
    {
        $distance = $this->geocode->distanceTo(43, -90, 'm');
        $this->assertLessThan(81336.0, $distance);
        $this->assertGreaterThan(81300.0, $distance);
    }

    public function testLongitudeLatitudeDistanceCalculation()
    {
        $distance = $this->geocode->distanceTo(43.001, -89.001, 'm');
        $this->assertLessThan(145.0, $distance);
        $this->assertGreaterThan(130.0, $distance);
    }
}
