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
     * @param string $in
     * @param int $duration
     * @return Schedule
     */
    protected function createSchedule(Carbon $date, string $in, int $duration): Schedule
    {
        return Schedule::create($this->makeSchedule($date, $in, $duration));
    }

    /**
     * Helper to make Schedule model data array.
     *
     * @param Carbon $date
     * @param string $in
     * @param int $duration
     * @return array
     */
    protected function makeSchedule(Carbon $date, string $in, int $duration): array
    {
        if (strlen($in) === 8) $in = $date->format('Y-m-d') . ' ' . $in;

        $data = factory(Schedule::class)->raw([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'starts_at' => $in,
            'duration' => $duration,
            'hours_type' => 'default',
            'fixed_rates' => 0,
            'service_id' => $this->service->id,
        ]);

        return $data;
    }

    /**
     * Helper to create a service breakout Schedule.
     *
     * @param Carbon $date
     * @param string $in
     * @param int $services
     * @param int $hoursPerService
     * @return Schedule
     */
    public function createServiceBreakoutSchedule(Carbon $date, string $in, int $services, int $hoursPerService): Schedule
    {
        $duration = $services * $hoursPerService;
        $data = $this->makeSchedule($date, $in, $duration);
        $data['service_id'] = null;

        $schedule = Schedule::create($data);
        factory(ScheduleService::class, $services)->create([
            'schedule_id' => $schedule->id,
            'duration' => $hoursPerService,
        ]);

        return $schedule->fresh();
    }
}
