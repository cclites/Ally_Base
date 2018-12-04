<?php
namespace App\Shifts;

use App\Shift;
use App\ShiftFlag;

class ShiftFlagManager
{
    /**
     * Return false when internal queries are being committed to prevent never-ending loops
     *
     * @param \App\Shift $shift
     * @return bool
     */
    public function shouldGenerate(Shift $shift)
    {
        return $shift->checked_in_time
                && $shift->checked_out_time
                && $shift->isDirty()
                && !$shift->isDirty('duplicated_by');
    }

    /**
     * Check all available flags and save the applicable flags to the shift
     *
     * @param \App\Shift $shift
     */
    public function generateFlags(Shift $shift)
    {
        $flags = $this->getFlags($shift);
        $shift->syncFlags($flags);

        if ($this->isDuplicate($shift)) {
            $this->attachDuplicates($shift);
        }
    }

    /**
     * Return an array of flags that match the Shift details
     * Checks all is"Flag"() methods for a boolean value
     *
     * @param \App\Shift $shift
     * @return array
     */
    public function getFlags(Shift $shift)
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
        return $this->duplicateQuery($shift)->exists();
    }

    public function isModified(Shift $shift)
    {
        $requiredUpdates = in_array($shift->checked_in_method, [Shift::METHOD_TELEPHONY, Shift::METHOD_GEOLOCATION]) ? 2 : 1;

        return $shift->audits()->where('event', 'updated')
                ->where('new_values', 'NOT LIKE', '{"status"%') // skip status updates
                ->where('new_values', '!=', '[]') // skip empty updates
                ->count() >= $requiredUpdates;
    }

    public function isOutsideAuth(Shift $shift)
    {
        return false; // TODO
    }

    public function isTimeExcessive(Shift $shift)
    {
        return $shift->duration() > 24;
    }

    public function getDuplicates(Shift $shift)
    {
        return $this->duplicateQuery($shift)->get();
    }

    public function attachDuplicates(Shift $shift)
    {
        $duplicates = $this->getDuplicates($shift);
        $shift->update(['duplicated_by' => $duplicates->first()->id]);
        foreach($duplicates as $duplicate) {
            if (!$duplicate->duplicated_by) {
                $duplicate->addFlag('duplicate');
                $duplicate->update(['duplicated_by' => $shift->id]);
            }
        }
    }

    protected function duplicateQuery(Shift $shift)
    {
        return Shift::where('id', '!=', $shift->id)
            ->where('caregiver_id', $shift->caregiver_id)
            ->where('client_id', $shift->client_id)
            ->where(function($q) use ($shift) {
                // Exact Match
                $q->where('checked_in_time', $shift->checked_in_time)
                    ->where('checked_out_time', $shift->checked_out_time);

                // Outside of Hours
                $q->orWhere('checked_in_time', '>', $shift->checked_in_time)
                    ->where('checked_in_time', '<', $shift->checked_out_time);
                $q->orWhere('checked_out_time', '<', $shift->checked_out_time)
                    ->where('checked_out_time', '>', $shift->checked_in_time);

                // Inside of Hours
                $q->orWhereRaw("? > checked_in_time AND ? < checked_out_time", [$shift->checked_in_time, $shift->checked_in_time]);
                $q->orWhereRaw("? < checked_out_time AND ? > checked_in_time", [$shift->checked_out_time, $shift->checked_out_time]);
            });
    }
}