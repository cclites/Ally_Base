<?php

namespace Tests;

use App\Billing\ClientPayer;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Service;
use App\BusinessChain;
use App\Client;
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
     * @var \App\Billing\Payer
     */
    protected $payer;

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

    /**
     * Create a General service for the chain.
     *
     * @param BusinessChain $chain
     * @return mixed
     */
    public function createDefaultService(BusinessChain $chain) : Service
    {
        return factory(Service::class)->create([
            'chain_id' => $chain->id,
            'default' => true
        ]);
    }

    /**
     * Create a Payer and ClientPayer relationship.
     *
     * @return ClientPayer
     */
    public function createEffectivePayer() : ClientPayer
    {
        $this->payer = factory(Payer::class)->create();

        return factory(ClientPayer::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $this->payer->id
        ]);
    }
}
