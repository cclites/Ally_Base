<?php
namespace App\Shifts;

use App\PhoneNumber;
use App\Shifts\Contracts\ShiftDataInterface;

class TVVClockOutData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(
        PhoneNumber $phoneNumber,
        bool $verified
    ) {
        $this->attributes = [
            'checked_out_number' => $phoneNumber->national_number,
            'checked_out_verified' => $verified,
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