<?php

namespace App\Shifts;

use App\Events\ShiftModified;
use App\Events\UnverifiedShiftLocation;
use App\Events\UnverifiedShiftCreated;
use App\Exceptions\UnverifiedLocationException;
use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;

class ClockOut extends ClockBase
{
    protected $comments;
    protected $otherExpenses = 0;
    protected $otherExpensesDesc;
    protected $mileage = 0;
    protected $goals = [];

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function setOtherExpenses($amount, $desc = null)
    {
        $this->otherExpenses = $amount;
        if (!is_null($desc))
            $this->otherExpensesDesc = $desc;
    }

    public function setMileage($miles)
    {
        $this->mileage = round($miles);
    }

    public function setGoals($goals)
    {
        $this->goals = $goals;   
    }

    /**
     * @param Shift $shift
     * @param array $activities
     * @param ShiftIssue[] $issues
     *
     * @return bool
     * @throws \App\Exceptions\UnverifiedLocationException
     */
    public function clockOut(Shift $shift, $activities = [], $issues = [])
    {
        $this->attachActivities($shift, $activities);
        if ($issues) {
            foreach($issues as $issue) {
                $this->attachIssue($shift, $issue);
            }
        }

        // Determine whether this is a verified clock in attempt
        $verified = ($shift->verified && !$this->manual) ? true : false;

        // Attempt to verify EVV regardless of previous status,
        // but only throw the exception if it's an attempt at a verified clock in
        try {
            $this->verifyEVV($shift->client);
            $clockOutVerified = true;
        }
        catch (UnverifiedLocationException $e) {
            if ($verified) throw $e;
        }

        $update = $shift->update([
            'checked_out_method' => $this->getMethod(),
            'checked_out_time' => Carbon::now(),
            'checked_out_latitude' => $this->latitude,
            'checked_out_longitude' => $this->longitude,
            'checked_out_distance' => $this->distance,
            'checked_out_number' => $this->number,
            'checked_out_ip' => \Request::ip(),
            'checked_out_agent' => \Request::userAgent(),
            'checked_out_verified' => $clockOutVerified ?? false,
            'caregiver_comments' => $this->comments,
            'other_expenses' => $this->otherExpenses,
            'other_expenses_desc' => $this->otherExpensesDesc,
            'mileage' => $this->mileage,
            'verified' => $verified,
        ]);

        $this->attachGoals($shift, $this->goals);
        
        $shift->statusManager()->ackClockOut($verified);

        if (!$verified) {
            event(new UnverifiedShiftCreated($shift));
            if (!$this->number) {
                event(new UnverifiedShiftLocation($shift));
            }
        }

        return $update;
    }

    public function attachActivities(Shift $shift, $activities = []) {
        $activities = (array) $activities; // allow a single ID
        if (count($activities)) {
            $shift->activities()->attach($activities);
        }
        return true;
    }

    public function attachIssue(Shift $shift, ShiftIssue $issue) {
        return $shift->issues()->save($issue);
    }

    /**
     * Enuerate the goals submitted and attach a ShiftGoal for each
     * one that contains caregiver comments.
     *
     * @param Shift $shift
     * @param array $goals
     * @return bool
     */
    public function attachGoals(Shift $shift, $goals)
    {
        foreach($goals as $key => $val) {
            if (empty($val)) {
                continue;
            }

            $shift->goals()->create([
                'client_goal_id' => $key,
                'comments' => $val,
            ]);
        }

        return true;
    }
}