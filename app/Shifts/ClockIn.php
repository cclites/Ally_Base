<?php
namespace App\Shifts;

use App\Business;
use App\Client;
use App\Exceptions\UnverifiedLocationException;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use Carbon\Carbon;
use App\Events\UnverifiedClockIn;

class ClockIn extends ClockBase
{
    /**
     * Clock in to a specific schedule
     *
     * @param Schedule $schedule
     * @return Shift|false
     * @throws \App\Exceptions\InvalidScheduleParameters
     * @throws \App\Exceptions\UnverifiedLocationException
     */
    public function clockIn(Schedule $schedule)
    {
        $this->validateSchedule($schedule);

        $shiftData = [
            'business_id' => $schedule->business_id,
            'client_id' => $schedule->client_id,
            'schedule_id' => $schedule->id,
            'fixed_rates' => $schedule->fixed_rates,
            'caregiver_rate' => $schedule->getCaregiverRate(),
            'provider_fee' => $schedule->getProviderFee(),
            'address_id' => $schedule->address ? $schedule->address->id : null,
        ];
        $shift = $this->makeShift($shiftData);

        // Attempt to verify EVV regardless of previous status,
        // but only throw the exception if it's an attempt at a verified clock in (non-manual)
        try {
            $this->verifyEVV($schedule->client);
            $shift->checked_in_verified = true;
        }
        catch (UnverifiedLocationException $e) {
            event(new UnverifiedClockIn($shift));
            if ($shift->verified) throw $e;
        }

        $shift->checked_in_distance = $this->distance;

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

    /**
     * Clock in without a schedule
     *
     * @param \App\Business $business
     * @param \App\Client $client
     * @return \App\Shift|bool
     * @throws \App\Exceptions\UnverifiedLocationException
     */
    public function clockInWithoutSchedule(Business $business, Client $client)
    {
        // Find rates
        $rates = app(RateFactory::class)->getRatesForClientCaregiver($client, $this->caregiver, false);

        // Get address information
        $address = $client->addresses()->where('type', 'evv')->first();

        $shiftData = [
            'business_id' => $business->id,
            'client_id' => $client->id,
            'schedule_id' => null,
            'caregiver_rate' => $rates->caregiver_rate ?? 0,
            'provider_fee' => $rates->provider_fee ?? 0,
            'address_id' => $address ? $address->id : null,
        ];
        $shift = $this->makeShift($shiftData);

        // Attempt to verify EVV regardless of previous status,
        // but only throw the exception if it's an attempt at a verified clock in (non-manual)
        try {
            $this->verifyEVV($client);
            $shift->checked_in_verified = true;
        }
        catch (UnverifiedLocationException $e) {
            if ($shift->verified) throw $e;
        }

        $shift->checked_in_distance = $this->distance;

        if ($this->caregiver->shifts()->save($shift)) {
            return $shift;
        }
        return false;
    }

    protected function makeShift(array $options = [])
    {
        $shift = new Shift($options + [
            'checked_in_verified' => false,
            'checked_in_method' => $this->getMethod(),
            'checked_in_time' => Carbon::now(),
            'checked_in_latitude' => $this->latitude,
            'checked_in_longitude' => $this->longitude,
            'checked_in_number' => $this->number,
            'checked_in_ip' => \Request::ip(),
            'checked_in_agent' => \Request::userAgent(),
            'fixed_rates' => false,
            'status' => Shift::CLOCKED_IN,
        ]);
        return $shift;
    }
}
