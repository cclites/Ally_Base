<?php
namespace App\Traits;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\Shift;
use App\Timesheet;

trait ActiveBusiness
{
    protected $override;

    /**
     * @return \App\Business
     * @throws \Exception
     */
    protected function business()
    {
        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        if (!$business = $activeBusiness->get()) {
            throw new \Exception('No default business found.');
        }
        return $business;
    }

    /**
     * Return true if a business has access to the specified client or client id
     *
     * @param int|\App\Client $client
     * @return bool
     */
    protected function businessHasClient($client)
    {
        if (is_admin()) {
            // Set active business to client's related business and return true for admins
            if (!$client instanceof Client) {
                $client = Client::find($client);
            }
            $this->setBusinessAs($client->business);
            return true;
        }
        if ($client instanceof Client) {
            return $client->business_id == $this->business()->id;
        }
        return $this->business()->clients()->where('id', $client)->exists();
    }

    /**
     * Return true if a business has access to the specified caregiver or caregiver id
     *
     * @param int|\App\Caregiver $caregiver
     * @return bool
     */
    protected function businessHasCaregiver($caregiver)
    {
        if (is_admin()) {
            if (!$caregiver instanceof Caregiver) {
                $caregiver = Caregiver::find($caregiver);
            }
            if ($business = $caregiver->businesses->first()) {
                $this->setBusinessAs($business);
            }
            return true;
        }
        if ($caregiver instanceof Caregiver) {
            $caregiver = $caregiver->id;
        }
        return $this->business()->caregivers()->where('caregiver_id', $caregiver)->exists();
    }

    /**
     * Return true if a business has access to the specified shift or shift id
     *
     * @param int|\App\Shift $shift
     * @return bool
     */
    protected function businessHasShift($shift)
    {
        if (is_admin()) {
            // Set active business to shifts's related business and return true for admins
            if (!$shift instanceof Shift) {
                $shift = Shift::find($shift);
            }
            $this->setBusinessAs($shift->business);
            return true;
        }
        if ($shift instanceof Shift) {
            return $shift->business_id == $this->business()->id;
        }
        return $this->business()->shifts()->where('id', $shift)->exists();
    }

    /**
     * Return true if a business has access to the specified schedule or schedule id
     *
     * @param int|\App\Schedule $schedule
     * @return bool
     */
    protected function businessHasSchedule($schedule)
    {
        if (is_admin()) {
            // Set active business to schedules's related business and return true for admins
            if (!$schedule instanceof Schedule) {
                $schedule = Schedule::find($schedule);
            }
            $this->setBusinessAs($schedule->business);
            return true;
        }
        if ($schedule instanceof Schedule) {
            return $schedule->business_id == $this->business()->id;
        }
        return $this->business()->schedules()->where('id', $schedule)->exists();
    }

    /**
     * Return true if a business has access to the specified timesheet or timesheet id
     *
     * @param int|\App\Timesheet $timesheet
     * @return bool
     */
    protected function businessHasTimesheet($timesheet)
    {
        if (is_admin()) {
            // Set active business to shifts's related business and return true for admins
            if (!$timesheet instanceof Timesheet) {
                $timesheet = Timesheet::find($timesheet);
            }
            $this->setBusinessAs($timesheet->business);
            return true;
        }
        if ($timesheet instanceof Timesheet) {
            return $timesheet->business_id == $this->business()->id;
        }
        return $this->business()->timesheets()->where('id', $timesheet)->exists();
    }

    /**
     * @return string
     */
    protected function timezone()
    {
        return $this->business()->timezone ?? 'America/New_York';
    }

    /**
     * Override the active business (used for Admins)
     *
     * @param \App\Business $business
     */
    protected function setBusinessAs(Business $business) {
        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        $activeBusiness->set($business);
    }
}
