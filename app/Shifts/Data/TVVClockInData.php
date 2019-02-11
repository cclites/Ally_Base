<?php
namespace App\Shifts;

use App\PhoneNumber;
use App\Shifts\Contracts\ShiftDataInterface;

class TVVClockInData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(
        PhoneNumber $phoneNumber,
        bool $verified
    ) {
        $this->attributes = [
            'checked_in_number' => $phoneNumber->national_number,
            'checked_in_verified' => $verified,
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