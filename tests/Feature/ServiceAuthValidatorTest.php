<?php

namespace Tests\Feature;

use App\Schedule;
use App\Shift;
use App\Shifts\ServiceAuthValidator;
use Tests\CreatesBusinesses;
use Tests\CreatesSchedules;
use Tests\CreatesShifts;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\ClientRate;
use App\Billing\Service;
use Carbon\Carbon;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ClientAuthorization;
use App\Billing\Payer;

class ServiceAuthValidatorTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesSchedules, CreatesShifts;

    /**
     * @var \App\Service
     */
    protected $service;

    /**
     * @var \App\Shifts\ServiceAuthValidator
     */
    protected $validator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->service = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => true
        ]);

        $this->validator = new ServiceAuthValidator($this->client);
    }

    /**
     * Helper to create a ClientAuthorization.
     *
     * @param array $data
     * @return ClientAuthorization
     */
    public function createClientAuth(array $data) : ClientAuthorization
    {
        return factory(ClientAuthorization::class)->create(array_merge([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_MONTHLY,
            'effective_start' => Carbon::now()->setTimezone($this->client->getTimezone())->subYears(1)->toDateString(),
        ], $data));
    }

    /**
     * =============================================================================
     * SHIFTS                                                                 ======
     * =============================================================================
     */

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_a_service_breakout_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $shift2 = $this->createServiceBreakoutShift(Carbon::now(), '12:00:00', 3, 2);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function a_shift_should_exceed_clients_max_weekly_hours_based_on_the_period_the_shift_takes_place()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $date = Carbon::now()->subMonth(1)->startOfWeek();
        $data = $this->makeShift($date, '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $data = $this->makeShift($date, '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_weekly_auth_limits()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_monthly_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_MONTHLY,
        ]);

        $data = $this->makeShift(Carbon::now()->startOfMonth(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_daily_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_DAILY,
        ]);

        $data = $this->makeShift(Carbon::yesterday(), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::now(), '10:00:00', '15:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function it_should_not_fail_for_shifts_outside_the_service_auth_period()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now()->startOfWeek(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::now()->startOfWeek()->subDays(3), '12:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_a_specific_service_type()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        // shift with only 3 hours of specified service id should not flag yet
        $data = $this->makeShift(Carbon::now(), '11:00:00', '17:00:00');
        $shift = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
        ]);
        $otherService = factory(Service::class)->create(['chain_id' => $this->client->business->businessChain->id, 'default' => false]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $otherService->id,
        ]);
        $shift = $shift->fresh();

        $this->assertEquals(6, $shift->getBillableHours());
        $this->assertEquals(3, $shift->getBillableHours($this->service->id));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // a second shift with 3 more hours of specified service id should flag
        $data = $this->makeShift(Carbon::now(), '11:00:00', '15:00:00');
        $shift = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 1,
            'service_id' => $otherService->id,
        ]);
        $shift = $shift->fresh();

        $this->assertEquals(4, $shift->getBillableHours());
        $this->assertEquals(3, $shift->getBillableHours($this->service->id));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function a_service_breakout_shift_can_fail_on_any_payer()
    {
        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $this->createClientAuth([
            'payer_id' => null,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift = $this->createShift(Carbon::now(), '11:00:00', '17:00:00');
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $otherPayer->id,
        ]);
        $shift = $shift->fresh();

        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_a_specific_payer()
    {
        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $this->createClientAuth([
            'payer_id' => $payer->id,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        // shift with only 3 hours of specified payer id should not flag yet
        $data = $this->makeShift(Carbon::now(), '11:00:00', '17:00:00');
        $shift = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $otherPayer->id,
        ]);
        $shift = $shift->fresh();

        $this->assertEquals(6, $shift->getBillableHours());
        $this->assertEquals(3, $shift->getBillableHours(null, $payer->id));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // a second shift with 3 more hours of payer service id should flag
        $data = $this->makeShift(Carbon::now(), '11:00:00', '15:00:00');
        $shift2 = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift2->id,
            'duration' => 3,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift2->id,
            'duration' => 3,
            'payer_id' => $otherPayer->id,
        ]);
        $shift2 = $shift2->fresh();

        $this->assertEquals(6, $shift2->getBillableHours());
        $this->assertEquals(3, $shift2->getBillableHours(null, $payer->id));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function fixed_hour_shifts_should_fail_if_exceeding_service_auth_fixed_limit()
    {
        $this->createClientAuth([
            'units' => 2,
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::today(), '11:00:00', '13:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::today(), '03:01:00', '04:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::today(), '04:01:00', '05:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    public function it_should_only_fail_for_auths_effective_during_the_time_of_the_shift()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subYears(1),
            'effective_end' => Carbon::today()->subDays(1),
        ]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertCount(0, $this->client->getActiveServiceAuths($shift->checked_in_time));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift(Carbon::today()->subMonths(2), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertCount(1, $this->client->getActiveServiceAuths($shift->checked_in_time));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    public function it_fails_based_on_the_period_the_shift_takes_place()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $date = Carbon::now()->subMonth(1)->startOfWeek();

        $data = $this->makeShift($date, '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        $data = $this->makeShift($date->addDays(2), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    public function an_actual_hours_shift_should_fail_when_hours_exceed_full_term_limits()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_TERM,
            'effective_start' => Carbon::now()->subDays(5),
            'effective_end' => Carbon::now()->addYears(1),
        ]);

        // 4 hour shift inside the term dates
        $data = $this->makeShift(Carbon::yesterday(), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // shift should exceed the max unit but is outside the term dates
        $data = $this->makeShift(Carbon::now()->addYears(2), '10:00:00', '12:30:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // shift exceeds the max units and is inside the term dates - flag
        $data = $this->makeShift(Carbon::now()->addMonths(2), '10:00:00', '12:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift2));
    }

    /** @test */
    function an_actual_hours_shift_should_fail_when_hours_exceeds_specific_daily_limits()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 5,
            'tuesday' => 500,
        ]);

        // 4 hours shift on a monday -> no flag yet
        $data = $this->makeShift(Carbon::parse('last monday'), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // 2 hour shift on any other day -> still no flag
        $data = $this->makeShift(Carbon::parse('last tuesday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // 2 hours shift on the same monday -> should flag
        $data = $this->makeShift(Carbon::parse('last monday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function a_spec_day_auth_should_always_fail_on_days_marked_0()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 0,
        ]);

        $data = $this->makeShift(Carbon::parse('last monday'), '10:00:00', '11:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function it_fails_on_all_days_an_actual_hours_shift_extends_to()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 500,
            'tuesday' => 3,
        ]);

        // create a shift that is 1 hour on monday and 4 hours on tuesday
        $start = Carbon::parse('last monday 23:00:00', $this->client->getTimezone())->setTimezone('UTC');
        $end = $start->copy()->addHours(5)->setTimezone('UTC');
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function a_service_breakout_shift_should_count_total_service_hours_for_all_days_of_the_shift()
    {
        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $auth = $this->createClientAuth([
            'payer_id' => $payer->id,
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 3,
            'tuesday' => 500,
        ]);

        // shift with only 3 hours of specified payer id should not flag yet
        $start = Carbon::parse('last monday 23:00:00', $this->client->getTimezone())->setTimezone('UTC');
        $end = $start->copy()->addHours(8)->setTimezone('UTC');
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 4,
            'service_id' => $this->service->id,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 4,
            'service_id' => $this->service->id,
            'payer_id' => $otherPayer->id,
        ]);
        $shift = $shift->fresh();

        // shift should flag because 4 > 3 on monday
        $this->assertEquals(8, $shift->getBillableHours());
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));

        // auth should flag because same amount of hours on tuesday
        $auth->update([
            'monday' => 500,
            'tuesday' => 3,
        ]);
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function a_fixed_unit_type_client_auth_should_count_for_all_dates_a_shift_expands_to()
    {
        $this->createClientAuth([
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 2,
            'tuesday' => 1,
        ]);

        // create shift that expands from monday - tuesday
        $start = Carbon::parse('last monday 23:00:00', $this->client->getTimezone())->setTimezone('UTC');
        $end = $start->copy()->addHours(5)->setTimezone('UTC');
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['fixed_rates' => 1]));

        // no flag yet
        $this->assertNull($this->validator->exceededServiceAuthorization($shift));

        // create another shift on tuesday
        $start = $end->copy();
        $end = $start->copy()->addHours(3);
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['fixed_rates' => 1]));

        // should flag because tuesday now has 2
        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /** @test */
    function it_can_fail_for_service_auths_that_are_effective_on_the_second_day_of_the_shift()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_DAILY,
            'effective_start' => Carbon::tomorrow()->toDateString(),
            'effective_end' => Carbon::now()->addYears(1)->toDateString(),
        ]);

        $this->assertCount(0, $this->client->getActiveServiceAuths(Carbon::today($this->client->getTimezone())));
        $this->assertCount(1, $this->client->getActiveServiceAuths(Carbon::tomorrow(($this->client->getTimezone()))));

        $data = $this->makeShift(Carbon::today(), '23:00:00', '07:00:00', Carbon::tomorrow());
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));

        $this->assertNotNull($this->validator->exceededServiceAuthorization($shift));
    }

    /**
     * =============================================================================
     * SCHEDULES                                                              ======
     * =============================================================================
     */

    /** @test */
    function it_can_fail_based_on_an_actual_hour_schedule_exceeding_max_client_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', '03:00:00');
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 5);
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule));
    }

    /** @test */
    function it_can_fail_based_on_a_service_breakout_schedule_exceeding_max_client_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', '03:00:00');
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule2 = $this->createServiceBreakoutSchedule(Carbon::now(), '12:00:00', 3, 2);
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule2));
    }

    /** @test */
    function it_can_fail_based_on_a_schedule_that_has_not_yet_persisted()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', '03:00:00');
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule = Schedule::make($this->makeSchedule(Carbon::today(), '03:00:00', 5));
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule));
    }
}