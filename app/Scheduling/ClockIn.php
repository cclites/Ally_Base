<?php
namespace App\Scheduling;

use App\Client;
use App\Responses\ErrorResponse;
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
        ]);

        if ($shift->verified) {
            if (!is_null($this->latitude)) {
                $this->verifyGeocode($schedule->client);
            }
            else {
                $this->verifyPhoneNumber($schedule->client);
            }
        }

        $this->fillRates($shift, $schedule->client);

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

    protected function fillRates(Shift $shift, Client $client)
    {
        $relation = $client->caregivers()->find($this->caregiver->id);

        if (!$relation) {
            $caregiver_rate = 0;
            $provider_fee = 0;
        }
        else {
            if ($shift->all_day) {
                $caregiver_rate = $relation->pivot->caregiver_daily_rate;
                $provider_fee = $relation->pivot->provider_daily_fee;
            }
            else {
                $caregiver_rate = $relation->pivot->caregiver_hourly_rate;
                $provider_fee = $relation->pivot->provider_hourly_fee;
            }
        }

        $shift->fill([
            'caregiver_rate' => $caregiver_rate,
            'provider_fee' => $provider_fee
        ]);
    }
}