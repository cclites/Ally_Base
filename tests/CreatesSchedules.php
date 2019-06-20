<?php

namespace Tests;

use App\Billing\ScheduleService;
use App\Schedule;
use Carbon\Carbon;

/**
 * Trait CreatesSchedules
 * Requires local members service, client, caregiver
 * @package Tests
 */
trait CreatesSchedules
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
     * Helper to persist a Schedule.
     *
     * @param Carbon $date
     * @param string $time
     * @param int $hours
     * @param array $defaults
     * @return Schedule
     */
    protected function createSchedule(Carbon $date, string $time, int $hours, array $defaults = []): Schedule
    {
        return Schedule::create($this->makeSchedule($date, $time, $hours, $defaults));
    }

    /**
     * Helper to make Schedule model data array.
     *
     * @param Carbon $date
     * @param string $time
     * @param int $hours
     * @param array $defaults
     * @return array
     */
    protected function makeSchedule(Carbon $date, string $time, int $hours, array $defaults = []): array
    {
        $in = Carbon::parse($date->format('Y-m-d') . ' ' . $time, $this->client->getTimezone());

        $data = factory(Schedule::class)->raw(array_merge([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'starts_at' => $in,
            'duration' => $hours * 60,
            'hours_type' => 'default',
            'fixed_rates' => 0,
            'service_id' => $this->service->id,
        ], $defaults));

        return $data;
    }

    /**
     * Helper to create a service breakout Schedule.
     *
     * @param \Carbon\Carbon $date
     * @param string $time
     * @param array $serviceIds
     * @param float $hoursPerService
     * @return Schedule
     */
    public function createServiceBreakoutSchedule(Carbon $date, string $time, array $serviceIds, float $hoursPerService): Schedule
    {
        $hours = count($serviceIds) * $hoursPerService;
        $schedule = $this->createSchedule($date, $time, $hours, [
            'service_id' => null,
        ]);

        foreach ($serviceIds as $id) {
            factory(ScheduleService::class)->create([
                'schedule_id' => $schedule->id,
                'duration' => $hoursPerService,
                'service_id' => $id,
                'payer_id' => null,
            ]);
        }

        return $schedule->fresh();
    }

    /**
     * Helper to make a service breakout Schedule.
     *
     * @param \Carbon\Carbon $date
     * @param string $time
     * @param array $serviceIds
     * @param float $hoursPerService
     * @return array
     */
    public function makeServiceBreakoutSchedule(Carbon $date, string $time, array $serviceIds, float $hoursPerService): array
    {
        $hours = count($serviceIds) * $hoursPerService;
        $schedule = $this->makeSchedule($date, $time, $hours, [
            'service_id' => null,
        ]);

        $schedule['services'] = [];
        foreach ($serviceIds as $id) {
            array_push($schedule['services'], factory(ScheduleService::class)->raw([
                'schedule_id' => null,
                'duration' => $hoursPerService,
                'service_id' => $id,
                'payer_id' => null,
            ]));
        }

        return $schedule;
    }
}
