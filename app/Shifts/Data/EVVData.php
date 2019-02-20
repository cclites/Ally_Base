<?php


namespace App\Shifts\Data;


use App\Address;
use Packages\GMaps\GeocodeCoordinates;

class EVVData
{
    /**
     * @var \App\Address|null
     */
    public $address;
    /**
     * @var \Packages\GMaps\GeocodeCoordinates
     */
    public $coordinates;
    /**
     * @var bool
     */
    public $verified;
    /**
     * @var string|null
     */
    public $ipAddress;
    /**
     * @var string|null
     */
    public $userAgent;
    /**
     * @var float|null
     */
    public $distance;

    public function __construct(
        ?Address $address,
        GeocodeCoordinates $coordinates,
        bool $verified,
        ?float $distance = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $this->address = $address;
        $this->coordinates = $coordinates;
        $this->verified = $verified;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->distance = $distance;
    }
}