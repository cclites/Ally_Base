<?php
namespace App\Shifts;

use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\EVVData;

class EVVClockOutData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(EVVData $data)
    {
        $this->attributes = [
            'address_id' => $data->address->id ?? null,
            'checked_out_latitude' => $data->coordinates->latitude,
            'checked_out_longitude' => $data->coordinates->longitude,
            'checked_out_verified' => $data->verified,
            'checked_out_distance' => $data->distance,
            'checked_out_ip' => $data->ipAddress,
            'checked_out_agent' => $data->userAgent,
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