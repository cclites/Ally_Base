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
                $q->whereBetween('checked_in_time', [$shift->checked_in_time, $shift->checked_out_time])
                    ->orWhereBetween('checked_out_time', [$shift->checked_in_time, $shift->checked_out_time]);
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