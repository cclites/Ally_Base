<?php
namespace App\Shifts;

use App\Address;
use App\Shifts\Contracts\ShiftDataInterface;
use Packages\GMaps\GeocodeCoordinates;

class EVVClockOutData implements ShiftDataInterface
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
            'checked_out_latitude' => $coordinates->latitude,
            'checked_out_longitude' => $coordinates->longitude,
            'checked_out_verified' => $verified,
            'checked_out_ip' => $ipAddress,
            'checked_out_agent' => $userAgent,
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