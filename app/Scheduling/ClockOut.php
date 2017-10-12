<?php

namespace App\Scheduling;

use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;

class ClockOut extends ClockBase
{
    protected $comments;
    protected $otherExpenses = 0;
    protected $mileage = 0;

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function setOtherExpenses($amount)
    {
        $this->otherExpenses = $amount;
    }

    public function setMileage($miles)
    {
        $this->mileage = round($miles);
    }

    /**
     * @param Shift $shift
     * @param array $activities
     * @param ShiftIssue[] $issues
     *
     * @return bool
     */
    public function clockOut(Shift $shift, $activities = [], $issues = [])
    {
        $this->attachActivities($shift, $activities);
        if ($issues) {
            foreach($issues as $issue) {
                $this->attachIssue($shift, $issue);
            }
        }

        $update = $shift->update([
            'checked_out_time' => Carbon::now(),
            'checked_out_latitude' => $this->latitude,
            'checked_out_longitude' => $this->longitude,
            'checked_out_number' => $this->number,
            'caregiver_comments' => $this->comments,
            'other_expenses' => $this->otherExpenses,
            'mileage' => $this->mileage,
        ]);

        return $update;
    }

    public function attachActivities(Shift $shift, $activities = []) {
        $activities = (array) $activities; // allow a single ID
        if (count($activities)) {
            $shift->activities()->attach($activities);
        }
    }

    public function attachIssue(Shift $shift, ShiftIssue $issue) {
        return $shift->issues()->save($issue);
    }
}