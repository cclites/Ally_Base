<?php
namespace App\Shifts;

use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\TVVData;

class TVVClockInData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(TVVData $data)
    {
        $this->attributes = [
            'checked_in_number' => $data->phoneNumber,
            'checked_in_verified' => $data->verified,
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