<?php

namespace Tests;

use App\Billing\Invoiceable\ShiftService;
use App\Shift;
use Carbon\Carbon;

/**
 * Trait CreatesShifts
 * Requires local members service, client, caregiver
 * @package Tests
 */
trait CreatesShifts
{
    /**
     * @var \App\Client
     */
    public $client;

    /**
     * @var \App\Caregiver
     */
    public $caregiver;

    /**
     * @var \App\Service
     */
    protected $service;

    /**
     * Helper to persist a Shift.
     *
     * @param \Carbon\Carbon $date
     * @param string $time
     * @param float $hours
     * @param array $defaults
     * @return \App\Shift
     */
    protected function createShift(Carbon $date, string $time, float $hours, array $defaults = []): Shift
    {
        return Shift::create($this->makeShift($date, $time, $hours, $defaults));
    }

    /**
     * Helper to make a Shift model data array, automatically converting
     * the dates to the proper client timezone.
     *
     * @param \Carbon\Carbon $date
     * @param string $time
     * @param float $hours
     * @param array $defaults
     * @return array
     */
    protected function makeShift(Carbon $date, string $time, float $hours, array $defaults = []): array
    {
        $in = Carbon::parse($date->format('Y-m-d') . ' ' . $time, $this->client->getTimezone());
        $out = $in->copy()->addMinutes(($hours * 60));
        $data = factory(Shift::class)->raw(array_merge([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'checked_in_time' => $in->setTimezone('UTC'),
            'checked_out_time' => $out->setTimezone('UTC'),
            'hours_type' => 'default',
            'fixed_rates' => 0,
            'mileage' => 0,
            'other_expenses' => 0,
            'service_id' => $this->service->id,
        ], $defaults));

        return $data;
    }

    /**
     * Helper to create a service breakout Shift.
     *
     * @param \Carbon\Carbon $date
     * @param string $time
     * @param array $serviceIds
     * @param float $hoursPerService
     * @return Shift
     */
    public function createServiceBreakoutShift(Carbon $date, string $time, array $serviceIds, float $hoursPerService): Shift
    {
        $hours = count($serviceIds) * $hoursPerService;
        $shift = $this->createShift($date, $time, $hours, [
            'service_id' => null,
        ]);

        foreach ($serviceIds as $id) {
            factory(ShiftService::class)->create([
                'shift_id' => $shift->id,
                'service_id' => $id,
                'duration' => $hoursPerService,
            ]);
        }

        return $shift->fresh();
    }
}
