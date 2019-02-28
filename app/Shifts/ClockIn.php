<?php
namespace App\Shifts;

use App\Business;
use App\Client;
use App\Exceptions\UnverifiedLocationException;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\ClockData;
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
     */
    public function clockIn(Schedule $schedule)
    {
        $this->validateSchedule($schedule);

        $method = $this->getMethod();
        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData($method)
        );

        $data = $this->getClockInVerificationData($schedule->client);

        try {
            $shift = $factory->create($data);
            if (! $shift->checked_in_verified) {
                event(new UnverifiedClockIn($shift));
            }
            return $shift;
        }
        catch (\Exception $e) {}

        return false;
    }

    /**
     * Clock in without a schedule
     *
     * @param \App\Client $client
     * @return \App\Shift|bool
     */
    public function clockInWithoutSchedule(Client $client)
    {
        $method = $this->getMethod();
        $factory = ShiftFactory::withoutSchedule(
            $client,
            $this->caregiver,
            new ClockData($method)
        );

        $data = $this->getClockInVerificationData($client);
        try {
            $shift = $factory->create($data);
            return $shift;
        }
        catch (\Exception $e) {}

        return false;
    }
}
