<?php

namespace App\Shifts;

use App\Events\ShiftModified;
use App\Events\UnverifiedShiftLocation;
use App\Events\UnverifiedShiftCreated;
use App\Exceptions\UnverifiedLocationException;
use App\Shift;
use App\ShiftIssue;
use App\Shifts\Data\CaregiverClockoutData;
use App\Shifts\Data\ClockData;
use Carbon\Carbon;

class ClockOut extends ClockBase
{
    protected $comments;
    protected $otherExpenses = 0;
    protected $otherExpensesDesc;
    protected $mileage = 0;
    protected $goals = [];
    protected $questions = [];
    protected $answers = [];

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
        $this->mileage = round($miles, 2);
    }

    public function setGoals($goals)
    {
        $this->goals = $goals;   
    }

    public function setQuestions($answers, $questions)
    {
        $this->answers = $answers;   
        $this->questions = $questions;   
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
        $data[] = new CaregiverClockoutData(
            new ClockData($this->getMethod(), Carbon::now('UTC')),
            $this->mileage,
            $this->otherExpenses,
            $this->otherExpensesDesc,
            $this->comments
        );

        $data[] = $this->getClockOutVerificationData($shift->client);

        $shift->addData(...$data);
        $shift->verified = $shift->checked_in_verified && $shift->checked_out_verified;
        $update = $shift->save();

        if ($update) {
            $this->attachActivities($shift, $activities);
            if ($issues) {
                foreach($issues as $issue) {
                    $this->attachIssue($shift, $issue);
                }
            }
            $shift->syncGoals($this->goals);
            $shift->syncQuestions($this->questions, $this->answers);
            $shift->statusManager()->ackClockOut($shift->verified);

            if (!$shift->verified) {
                event(new UnverifiedShiftCreated($shift));
                if (!$this->number) {
                    event(new UnverifiedShiftLocation($shift));
                }
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
}
