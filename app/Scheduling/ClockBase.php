<?php
namespace App\Scheduling;

use App\Caregiver;
use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\Schedule;

abstract class ClockBase
{
    const MAXIMUM_DISTANCE_METERS = 150;
    protected $caregiver;
    protected $latitude;
    protected $longitude;
    protected $number;
    protected $manual = false;

    public function __construct(Caregiver $caregiver)
    {
        $this->caregiver = $caregiver;
    }

    public function setManual($manual = true)
    {
        $this->manual = $manual;
        return $this;
    }

    public function setGeocode($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        return $this;
    }

    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    protected function validateSchedule(Schedule $schedule)
    {
        if (!$schedule->client) {
            throw new InvalidScheduleParameters('No client was attached to this schedule.');
        }

        if ($schedule->caregiver_id != $this->caregiver->id) {
            throw new InvalidScheduleParameters('This caregiver is not assigned to this shift.');
        }
    }

    protected function verifyGeocode(Client $client)
    {
        if (!$client->evvAddress) throw new UnverifiedLocationException('Client does not have a service (EVV) address.');
        if ($client->evvAddress->distanceTo($this->latitude, $this->longitude, 'm') > self::MAXIMUM_DISTANCE_METERS) {
            throw new UnverifiedLocationException('Your location does not match the service address.');
        }
    }

    protected function verifyPhoneNumber(Client $client)
    {
        if (!$client->phoneNumbers()->where('national_number', $this->number)->exists()) {
            throw new UnverifiedLocationException('The phone number does not match the client record.');
        }
    }
}