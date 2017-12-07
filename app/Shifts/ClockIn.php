<?php
namespace App\Shifts;

use App\Business;
use App\Client;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;

class ClockIn extends ClockBase
{
    /**
     * @param Schedule $schedule
     * @return Shift|false
     */
    public function clockIn(Schedule $schedule)
    {
        $this->validateSchedule($schedule);

        $shift = new Shift([
            'business_id' => $schedule->business_id,
            'client_id' => $schedule->client_id,
            'schedule_id' => $schedule->id,
            'verified' => !$this->manual,
            'checked_in_time' => Carbon::now(),
            'checked_in_latitude' => $this->latitude,
            'checked_in_longitude' => $this->longitude,
            'checked_in_number' => $this->number,
            'all_day' => $schedule->all_day,
            'status' => Shift::CLOCKED_IN,
            'caregiver_rate' => $schedule->getCaregiverRate(),
            'provider_fee' => $schedule->getProviderFee()
        ]);

        if ($shift->verified) {
            if (!is_null($this->latitude)) {
                $this->verifyGeocode($schedule->client);
            }
            else {
                $this->verifyPhoneNumber($schedule->client);
            }
        }

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

    public function clockInWithoutSchedule(Business $business, Client $client)
    {
        // Find rates through relationship, if no rates, set to 0
        $relationship = $client->caregivers()->where('caregiver_id', $this->caregiver->id)->first();
        if ($relationship && isset($relationship->pivot)) {
            $rates = $relationship->pivot;
        }
        else {
            $rates = new \stdClass();
            $rates->caregiver_hourly_rate = 0;
            $rates->provider_hourly_fee = 0;
        }

        $shift = new Shift([
            'business_id' => $business->id,
            'client_id' => $client->id,
            'schedule_id' => null,
            'verified' => !$this->manual,
            'checked_in_time' => Carbon::now(),
            'checked_in_latitude' => $this->latitude,
            'checked_in_longitude' => $this->longitude,
            'checked_in_number' => $this->number,
            'all_day' => false,
            'status' => Shift::CLOCKED_IN,
            'caregiver_rate' => $rates->caregiver_hourly_rate,
            'provider_fee' => $rates->provider_hourly_fee
        ]);

        if ($shift->verified) {
            if (!is_null($this->latitude)) {
                $this->verifyGeocode($client);
            } else {
                $this->verifyPhoneNumber($client);
            }
        }

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

}