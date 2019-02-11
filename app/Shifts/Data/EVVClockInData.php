<?php
namespace App\Shifts;

use App\Address;
use App\Shifts\Contracts\ShiftDataInterface;
use Packages\GMaps\GeocodeCoordinates;

class EVVClockInData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(
        Address $address,
        GeocodeCoordinates $coordinates,
        bool $verified,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $this->attributes = [
            'address_id' => $address->id,
            'checked_in_latitude' => $coordinates->latitude,
            'checked_in_longitude' => $coordinates->longitude,
            'checked_in_verified' => $verified,
            'checked_in_ip' => $ipAddress,
            'checked_in_agent' => $userAgent,
        ];
    }


    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}