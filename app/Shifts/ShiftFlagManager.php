<?php
namespace App\Shifts;

use App\Shift;
use App\ShiftFlag;
use Illuminate\Support\Str;

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
     * Check all available flags and save the applicable flags to the shift.
     *
     * @param array $flags
     * return void
     */
    public function generate(array $flags = null) : void
    {
        if ($this->shift->statusManager()->isClockedIn() || empty($this->shift->checked_out_time)) {
            // do not process flags on shifts that have not been clocked out yet
            return;
        }

        if (empty($flags)) {
            $flags = ShiftFlag::FLAGS;
        }

        $this->shift->syncFlags($this->getFlags($flags));

        if ($this->isDuplicate()) {
            $this->attachDuplicates();
        }
    }

    /**
     * Return an array of flags that match the Shift details
     * Checks all is"Flag"() methods for a boolean value
     *
     * @param array|null $flagsToCheck
     * @return array
     */
    public function getFlags(?array $flagsToCheck = null) : array
    {
        $flags = [];
        foreach($flagsToCheck as $flag) {
            $method = 'is' . Str::studly($flag);
            if (method_exists($this, $method) && $this->$method($this->shift)) {
                $flags[] = $flag;
            }
        }

        return $flags;
    }

    /**
     * Handle 'added' flag check.
     *
     * @return bool
     */
    public function isAdded() : bool
    {
        return in_array($this->shift->checked_in_method, [Shift::METHOD_OFFICE, Shift::METHOD_UNKNOWN, Shift::METHOD_TIMESHEET]);
    }

    /**
     * Handle 'converted' flag check.
     *
     * @return bool
     */
    public function isConverted() : bool
    {
        return $this->shift->checked_in_method === Shift::METHOD_CONVERTED;
    }

    /**
     * Handle 'duplicate' flag check.
     *
     * @return bool
     */
    public function isDuplicate() : bool
    {
        return $this->duplicateQuery()->exists();
    }

    /**
     * Handle 'modified' flag check.
     *
     * @return bool
     */
    public function isModified() : bool
    {
        // This has been disabled due to poor performance in our
        // audits system.
        return false;
        $requiredUpdates = in_array($this->shift->checked_in_method, [Shift::METHOD_TELEPHONY, Shift::METHOD_GEOLOCATION]) ? 2 : 1;

        return $this->shift->audits()->where('event', 'updated')
                ->where('new_values', 'NOT LIKE', '{"status"%') // skip status updates
                ->where('new_values', '!=', '[]') // skip empty updates
                ->count() >= $requiredUpdates;
    }

    /**
     * Handle 'outside_auth' flag check.
     *
     * @return bool
     */
    public function isOutsideAuth() : bool
    {
        $validator = new ServiceAuthValidator($this->shift->client);

        if ($validator->shiftExceedsMaxClientHours($this->shift)) {
            return true;
        }

        if ($auth = $validator->shiftExceedsServiceAuthorization($this->shift)) {
            return true;
        }

        return false;
    }

    /**
     * Handle 'time_excessive' flag check.
     *
     * @return bool
     */
    public function isTimeExcessive() : bool
    {
        return $this->shift->duration() > 24;
    }

    /**
     * Handle 'duration_mismatch' flag check.
     *
     * @return bool
     */
    public function isDurationMismatch() : bool
    {
        if ($this->shift->fixed_rates || ! empty($this->shift->service_id)) {
            // actual hours shift
            return false;
        } else if (! empty($this->shift->services)) {
            // service breakout shift
            return $this->shift->duration() != floatval($this->shift->services->sum('duration'));
        }

        return false;
    }

    public function isMissingRates() : bool
    {
        return $this->shift->costs()->getClientCost(false) == 0;
    }

    /**
     * Get all duplicates of the shift.
     *
     * @return Shift[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getDuplicates() : ?iterable
    {
        return $this->duplicateQuery()->get();
    }

    /**
     * Attach duplicates to the shift.
     *
     * @return void
     */
    public function attachDuplicates() : void
    {
        $duplicates = $this->getDuplicates();
        $this->shift->update(['duplicated_by' => $duplicates->first()->id]);
        foreach($duplicates as $duplicate) {
            if (!$duplicate->duplicated_by) {
                $duplicate->addFlag('duplicate');
                $duplicate->update(['duplicated_by' => $this->shift->id]);
            }
        }
    }

    /**
     * Get the query for all duplicates of the shift.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function duplicateQuery() : \Illuminate\Database\Eloquent\Builder
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