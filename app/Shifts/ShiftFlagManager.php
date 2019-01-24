<?php
namespace App\Shifts;

use App\Shift;
use App\ShiftFlag;

class ShiftFlagManager
{
    /**
     * @var \App\Shift
     */
    public $shift;

    /**
     * Create a new instance.
     *
     * @param \App\Shift $shift
     */
    public function __construct(Shift $shift)
    {
        $this->shift = $shift;
    }

    /**
     * Check all available flags and save the applicable flags to the shift
     *
     * return void
     */
    public function generate(array $flags = null) : void
    {
        if (empty($flags)) {
            $flags = ShiftFlag::FLAGS;
        }

        $this->shift->syncFlags($this->getFlags());

        if ($this->isDuplicate()) {
            $this->attachDuplicates();
        }
    }

    /**
     * Return an array of flags that match the Shift details
     * Checks all is"Flag"() methods for a boolean value
     *
     * @return array
     */
    public function getFlags()
    {
        $flags = [];
        foreach(ShiftFlag::FLAGS as $flag) {
            $method = 'is' . studly_case($flag);
            if (method_exists($this, $method) && $this->$method($this->shift)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }

    public function isAdded()
    {
        return in_array($this->shift->checked_in_method, [Shift::METHOD_OFFICE, Shift::METHOD_UNKNOWN, Shift::METHOD_TIMESHEET]);
    }

    public function isConverted()
    {
        return $this->shift->checked_in_method === Shift::METHOD_CONVERTED;
    }

    public function isDuplicate()
    {
        return $this->duplicateQuery($this->shift)->exists();
    }

    public function isModified()
    {
        $requiredUpdates = in_array($this->shift->checked_in_method, [Shift::METHOD_TELEPHONY, Shift::METHOD_GEOLOCATION]) ? 2 : 1;

        return $this->shift->audits()->where('event', 'updated')
                ->where('new_values', 'NOT LIKE', '{"status"%') // skip status updates
                ->where('new_values', '!=', '[]') // skip empty updates
                ->count() >= $requiredUpdates;
    }

    public function isOutsideAuth()
    {
        return false; // TODO
    }

    public function isTimeExcessive()
    {
        return $this->shift->duration() > 24;
    }

    public function getDuplicates()
    {
        return $this->duplicateQuery($this->shift)->get();
    }

    public function attachDuplicates()
    {
        $duplicates = $this->getDuplicates($this->shift);
        $this->shift->update(['duplicated_by' => $duplicates->first()->id]);
        foreach($duplicates as $duplicate) {
            if (!$duplicate->duplicated_by) {
                $duplicate->addFlag('duplicate');
                $duplicate->update(['duplicated_by' => $this->shift->id]);
            }
        }
    }

    protected function duplicateQuery()
    {
        return Shift::where('id', '!=', $this->shift->id)
            ->where('caregiver_id', $this->shift->caregiver_id)
            ->where('client_id', $this->shift->client_id)
            ->where(function($q) {
                // Exact Match
                $q->where('checked_in_time', $this->shift->checked_in_time)
                    ->where('checked_out_time', $this->shift->checked_out_time);

                // Outside of Hours
                $q->orWhere('checked_in_time', '>', $this->shift->checked_in_time)
                    ->where('checked_in_time', '<', $this->shift->checked_out_time);
                $q->orWhere('checked_out_time', '<', $this->shift->checked_out_time)
                    ->where('checked_out_time', '>', $this->shift->checked_in_time);

                // Inside of Hours
                $q->orWhereRaw("? > checked_in_time AND ? < checked_out_time", [$this->shift->checked_in_time, $this->shift->checked_in_time]);
                $q->orWhereRaw("? < checked_out_time AND ? > checked_in_time", [$this->shift->checked_out_time, $this->shift->checked_out_time]);
            });
    }
}