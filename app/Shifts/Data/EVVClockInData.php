<?php
namespace App\Shifts;

use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\EVVData;

class EVVClockInData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(EVVData $data)
    {
        $this->attributes = [
            'address_id' => $data->address->id ?? null,
            'checked_in_latitude' => $data->coordinates->latitude,
            'checked_in_longitude' => $data->coordinates->longitude,
            'checked_in_verified' => $data->verified,
            'checked_in_distance' => $data->distance,
            'checked_in_ip' => $data->ipAddress,
            'checked_in_agent' => $data->userAgent,
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