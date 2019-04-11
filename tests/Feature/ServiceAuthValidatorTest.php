<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\Schedule;
use App\Shift;
use App\Shifts\ServiceAuthValidator;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\ShiftFlag;
use App\ShiftStatusHistory;
use App\Billing\ClientRate;
use App\Billing\Service;
use Carbon\Carbon;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ClientAuthorization;
use App\Billing\Payer;

class ServiceAuthValidatorTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    /**
     * @var \App\Service
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->service = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => true
        ]);
        factory(ClientRate::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
        ]);
    }

    /**
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

    /**
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
     * Ensure max client weekly hours limit is set properly
     * in order to prevent false-positives.
     *
     * @param int $hours
     */
    public function assertClientMaxWeeklyHours(int $hours) : void
    {
        $this->client->update(['max_weekly_hours' => $hours]);
        $this->assertEquals($hours, $this->client->fresh()->max_weekly_hours);
    }

    /**
     * Helper to call the ServiceAuthValidator method.
     *
     * @param \App\Shift $shift
     * @return bool
     */
    public function exceedsMaxClientHours(Shift $shift) : bool
    {
        $validator = new ServiceAuthValidator($shift);
        return $validator->exceedsMaxClientHours();
    }

    /**
     * Helper to call the ServiceAuthValidator method.
     *
     * @param \App\Schedule $schedule
     * @return bool
     */
    public function scheduleExceedsMaxClientHours(Schedule $schedule) : bool
    {
        $validator = new ServiceAuthValidator($shift);
        return $validator->exceedsMaxClientHours();
    }

    /**
     * Helper to call the ServiceAuthValidator method.
     *
     * @param \App\Shift $shift
     * @return null|ClientAuthorization
     */
    public function exceededServiceAuth(Shift $shift) : ?ClientAuthorization
    {
        $validator = new ServiceAuthValidator($shift);
        return $validator->exceededServiceAuthorization();
    }

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_clients_max_weekly_hours()
    {
        $this->assertClientMaxWeeklyHours(10);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->exceedsMaxClientHours($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $this->assertTrue($this->exceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_a_service_breakout_shift_exceeds_clients_max_weekly_hours()
    {
        $this->assertClientMaxWeeklyHours(10);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->exceedsMaxClientHours($shift));

        $shift2 = $this->createServiceBreakoutShift(Carbon::now(), '12:00:00', 3, 2);
        $this->assertTrue($this->exceedsMaxClientHours($shift2));
    }

    /** @test */
    public function a_shift_should_exceed_clients_max_weekly_hours_based_on_the_period_the_shift_takes_place()
    {
        $this->assertClientMaxWeeklyHours(10);

        $date = Carbon::now()->subMonth(1)->startOfWeek();
        $data = $this->makeShift($date, '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $this->assertFalse($this->exceedsMaxClientHours($shift));

        $data = $this->makeShift($date, '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $this->assertTrue($this->exceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_weekly_auth_limits()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 10,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_monthly_service_auth_limits()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 10,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_MONTHLY,
        ]);

        $data = $this->makeShift(Carbon::now()->startOfMonth(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_daily_service_auth_limits()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 5,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_DAILY,
        ]);

        $data = $this->makeShift(Carbon::yesterday(), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::now(), '10:00:00', '15:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function it_should_not_fail_for_shifts_outside_the_service_auth_period()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 10,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now()->startOfWeek(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::now()->startOfWeek()->subDays(3), '12:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_any_service_type()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 10,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $shift2 = $this->createServiceBreakoutShift(Carbon::now(), '12:00:00', 3, 2);
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_a_specific_service_type()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 5,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
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
        $this->assertNull($this->exceededServiceAuth($shift));

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
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_a_specific_payer()
    {
        $this->assertClientMaxWeeklyHours(999);

        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $payer->id,
            'units' => 5,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
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
        $this->assertNull($this->exceededServiceAuth($shift));

        // a second shift with 3 more hours of payer service id should flag
        $data = $this->makeShift(Carbon::now(), '11:00:00', '15:00:00');
        $shift = Shift::create(array_merge($data, ['service_id' => null]));
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'payer_id' => $otherPayer->id,
        ]);
        $shift = $shift->fresh();

        $this->assertEquals(6, $shift->getBillableHours());
        $this->assertEquals(3, $shift->getBillableHours(null, $payer->id));
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    public function fixed_hour_shifts_should_fail_if_exceeding_service_auth_fixed_limit()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 2,
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::today(), '11:00:00', '13:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::today(), '03:01:00', '04:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::today(), '04:01:00', '05:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    public function it_should_only_fail_for_auths_effective_during_the_time_of_the_shift()
    {
        $this->assertClientMaxWeeklyHours(999);

        factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 5,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subYears(1),
            'effective_end' => Carbon::today()->subDays(1),
        ]);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertCount(0, $shift->getActiveServiceAuths());
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift(Carbon::today()->subMonths(2), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertCount(1, $shift->getActiveServiceAuths());
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    public function it_fails_based_on_the_period_the_shift_takes_place()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 10,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $date = Carbon::now()->subMonth(1)->startOfWeek();

        $data = $this->makeShift($date, '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        $data = $this->makeShift($date->addDays(2), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    public function an_actual_hours_shift_should_fail_when_hours_exceed_full_term_limits()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 5,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_TERM,
            'effective_start' => Carbon::now()->subDays(5),
            'effective_end' => Carbon::now()->addYears(1),
        ]);

        // 4 hour shift inside the term dates
        $data = $this->makeShift(Carbon::yesterday(), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        // shift should exceed the max unit but is outside the term dates
        $data = $this->makeShift(Carbon::now()->addYears(2), '10:00:00', '12:30:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        // shift exceeds the max units and is inside the term dates - flag
        $data = $this->makeShift(Carbon::now()->addMonths(2), '10:00:00', '12:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift2));
    }

    /** @test */
    function an_actual_hours_shift_should_fail_when_hours_exceeds_specific_daily_limits()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 5,
            'tuesday' => 500,
        ]);

        // 4 hours shift on a monday -> no flag yet
        $data = $this->makeShift(Carbon::parse('last monday'), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        // 2 hour shift on any other day -> still no flag
        $data = $this->makeShift(Carbon::parse('last tuesday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNull($this->exceededServiceAuth($shift));

        // 2 hours shift on the same monday -> should flag
        $data = $this->makeShift(Carbon::parse('last monday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    function a_spec_day_auth_should_always_fail_on_days_marked_0()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 0,
        ]);

        $data = $this->makeShift(Carbon::parse('last monday'), '10:00:00', '11:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    function it_fails_on_all_days_an_actual_hours_shift_extends_to()
    {
        $this->assertClientMaxWeeklyHours(999);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 500,
            'tuesday' => 3,
        ]);

        // create a shift that is 1 hour on monday and 4 hours on tuesday
        $start = Carbon::parse('last monday 23:00:00', $this->client->getTimezone())->setTimezone('UTC');
        $end = $start->copy()->addHours(5)->setTimezone('UTC');
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    function a_service_breakout_shift_should_count_total_service_hours_for_all_days_of_the_shift()
    {
        $this->assertClientMaxWeeklyHours(999);

        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $auth = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => $payer->id,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
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
        $this->assertNotNull($this->exceededServiceAuth($shift));

        // auth should flag because same amount of hours on tuesday
        $auth->update([
            'monday' => 500,
            'tuesday' => 3,
        ]);
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

    /** @test */
    function a_fixed_unit_type_client_auth_should_count_for_all_dates_a_shift_expands_to()
    {
        $this->assertClientMaxWeeklyHours(999);

        factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
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
        $this->assertNull($this->exceededServiceAuth($shift));

        // create another shift on tuesday
        $start = $end->copy();
        $end = $start->copy()->addHours(3);
        $data = $this->makeShift(Carbon::now(), $start->toDateTimeString(), $end->toDateTimeString());
        $shift = Shift::create(array_merge($data, ['fixed_rates' => 1]));

        // should flag because tuesday now has 2
        $this->assertNotNull($this->exceededServiceAuth($shift));
    }

}