<?php
namespace App\Shifts;

use App\Caregiver;
use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\Schedule;
use App\Shift;

abstract class ClockBase
{
    const MAXIMUM_DISTANCE_METERS = 804; // 0.5mi
    protected $caregiver;
    protected $latitude;
    protected $longitude;
    protected $number;
    protected $distance;
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

        $distance = $client->evvAddress->distanceTo($this->latitude, $this->longitude, 'm');

        if ($distance === false) {
            throw new UnverifiedLocationException('Your location was unable to be verified.');
        }

        $this->setDistance($distance);
        if ($distance > self::MAXIMUM_DISTANCE_METERS) {
            throw new UnverifiedLocationException('Your location does not match the service address.');
        }
    }

    protected function verifyPhoneNumber(Client $client)
    {
        if (!$client->phoneNumbers()->where('national_number', $this->number)->exists()) {
            throw new UnverifiedLocationException('The phone number does not match the client record.');
        }
    }

    protected function verifyEVV(Client $client)
    {
        if (!is_null($this->latitude)) {
            $this->verifyGeocode($client);
        } else {
            $this->verifyPhoneNumber($client);
        }
    }

    protected function setDistance($meters)
    {
        $this->distance = $meters;
    }

    protected function getMethod()
    {
        if ($this->number) {
            return Shift::METHOD_TELEPHONY;
        }
        return Shift::METHOD_GEOLOCATION;
    }
}