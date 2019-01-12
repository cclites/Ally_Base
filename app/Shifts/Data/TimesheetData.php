<?php


namespace App\Shifts\Data;


use App\Shifts\Contracts\ShiftDataInterface;
use App\Timesheet;

class TimesheetData implements ShiftDataInterface
{
    protected $attributes;

    function __construct(Timesheet $timesheet)
    {
        $this->attributes = [
            'timesheet_id' => $timesheet->id,
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