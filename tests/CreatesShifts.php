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
     * @param string $in
     * @param string $out
     * @param null|\Carbon\Carbon $endDate
     * @return \App\Shift
     */
    protected function createShift(Carbon $date, string $in, string $out, ?Carbon $endDate = null): Shift
    {
        return Shift::create($this->makeShift($date, $in, $out, $endDate));
    }

    /**
     * Helper to make a Shift model data array.
     *
     * @param \Carbon\Carbon $date
     * @param string $in
     * @param string $out
     * @param null|\Carbon\Carbon $endDate
     * @return array
     */
    protected function makeShift(Carbon $date, string $in, string $out, ?Carbon $endDate = null): array
    {
        if (empty($endDate)) {
            $endDate = $date;
        }
        if (strlen($in) === 8) $in = $date->format('Y-m-d') . ' ' . $in;
        if (strlen($out) === 8) $out = $endDate->format('Y-m-d') . ' ' . $out;

        $data = factory(Shift::class)->raw([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'checked_in_time' => $in,
            'checked_out_time' => $out,
            'hours_type' => 'default',
            'fixed_rates' => 0,
            'mileage' => 0,
            'other_expenses' => 0,
            'service_id' => $this->service->id,
        ]);

        return $data;
    }

    /**
     * Helper to create a service breakout Shift.
     *
     * @param \Carbon\Carbon $date
     * @param string $in
     * @param int $services
     * @param int $hoursPerService
     * @return Shift
     */
    public function createServiceBreakoutShift(Carbon $date, string $in, int $services, int $hoursPerService): Shift
    {
        $out = Carbon::parse($date->format('Y-m-d') . ' ' . $in)->addHours($services * $hoursPerService)->toTimeString();
        $data = $this->makeShift($date, $in, $out);

        $data['service_id'] = null;

        $shift = Shift::create($data);
        factory(ShiftService::class, $services)->create([
            'shift_id' => $shift->id,
            'duration' => $hoursPerService,
        ]);

        return $shift->fresh();
    }
}
