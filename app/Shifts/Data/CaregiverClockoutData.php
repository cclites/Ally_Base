<?php
namespace App\Shifts\Data;

use App\Shifts\Contracts\ShiftDataInterface;

class CaregiverClockoutData implements ShiftDataInterface
{
    protected $attributes;

    public function __construct(
        ClockData $clockOut,
        float $mileage,
        float $otherExpenses,
        ?string $expenseDescription = null,
        ?string $caregiverComments = null
    ) {
        $this->attributes = [
            'checked_out_method' => $clockOut->method,
            'checked_out_time' => $clockOut->time,
            'mileage' => $mileage,
            'other_expenses' => $otherExpenses,
            'other_expenses_desc' => $expenseDescription,
            'caregiver_comments' => $caregiverComments,
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