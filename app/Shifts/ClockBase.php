<?php
namespace App\Shifts;

use App\Caregiver;
use App\Client;
use App\Exceptions\InvalidScheduleParameters;
use App\Exceptions\UnverifiedLocationException;
use App\PhoneNumber;
use App\Schedule;
use App\Shift;
use App\Shifts\Contracts\ShiftDataInterface;
use App\Shifts\Data\EVVData;
use App\Shifts\Data\TVVData;
use Packages\GMaps\GeocodeCoordinates;

abstract class ClockBase
{
    /**
     * The default maximum radius in meters for geolocation verification
     */
    const MAXIMUM_DISTANCE_METERS = 300;

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

    public function getMethod()
    {
        if ($this->number) {
            return Shift::METHOD_TELEPHONY;
        }
        return Shift::METHOD_GEOLOCATION;
    }

    protected function buildVerificationData(Client $client)
    {
        if ($this->getMethod() === Shift::METHOD_TELEPHONY) {
            return new TVVData($this->number, $client->phoneNumbers()->where('national_number', $this->number)->exists());
        }

        $address = $client->getAddress();
        $distance = null;
        $verified = false;
        if ($address) {
            $distance = $address->distanceTo($this->latitude, $this->longitude, 'm');
            $verified = is_numeric($distance) && $distance <= self::MAXIMUM_DISTANCE_METERS;
        }

        return new EVVData(
            $address,
            new GeocodeCoordinates($this->latitude, $this->longitude),
            $verified,
            $distance !== false  ? $distance : null,
            request()->ip(),
            request()->userAgent()
        );
    }

    protected function getClockInVerificationData(Client $client): ShiftDataInterface
    {
        $data = $this->buildVerificationData($client);
        if ($data instanceof TVVData) {
            return new TVVClockInData($data);
        }
        return new EVVClockInData($data);
    }

    protected function getClockOutVerificationData(Client $client): ShiftDataInterface
    {
        $data = $this->buildVerificationData($client);
        if ($data instanceof TVVData) {
            return new TVVClockOutData($data);
        }
        return new EVVClockOutData($data);
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
}
