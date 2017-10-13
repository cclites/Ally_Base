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

        $shift->fill([
            'caregiver_rate' => $schedule->getCaregiverRate(),
            'provider_fee' => $schedule->getProviderFee()
        ]);

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

}