<?php
namespace App\Shifts;

use App\Shift;
use App\ShiftFlag;

class ShiftFlagManager
{
    /**
     * Return an array of flags that match the Shift details
     *
     * @param \App\Shift $shift
     * @return array
     */
    public function generateFlags(Shift $shift)
    {
        $flags = [];
        foreach(ShiftFlag::FLAGS as $flag) {
            $method = 'is' . studly_case($flag);
            if (method_exists($this, $method) && $this->$method($shift)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }

    public function isAdded(Shift $shift)
    {
        return in_array($shift->checked_in_method, [Shift::METHOD_OFFICE, Shift::METHOD_UNKNOWN, Shift::METHOD_TIMESHEET]);
    }

    public function isConverted(Shift $shift)
    {
        return $shift->checked_in_method === Shift::METHOD_CONVERTED;
    }

    public function isDuplicate(Shift $shift)
    {
        return Shift::where('id', '!=', $shift->id)
            ->where('caregiver_id', $shift->caregiver_id)
            ->where('client_id', $shift->client_id)
            ->where(function($q) use ($shift) {
                $checkedIn = $shift->checked_in_time->copy()->addMinutes(5)->toDateTimeString();
                $checkedOut = $shift->checked_in_time->copy()->subMinutes(5)->toDateTimeString();
                $q->whereBetween('checked_in_time', [$checkedIn, $checkedOut])
                    ->orWhereBetween('checked_out_time', [$checkedIn, $checkedOut]);
            })->exists();
    }

    public function isModified(Shift $shift)
    {
        return $shift->audits()->where('event', 'updated')
                ->where('new_values', 'NOT LIKE', '{"status"%') // skip status updates
                ->where('new_values', '!=', '[]') // skip empty updates
                ->count() > 1;
    }

    public function isOutsideAuth(Shift $shift)
    {
        return false; // TODO
    }

    public function isTimeExcessive(Shift $shift)
    {
        return $shift->duration() > 24;
    }
}