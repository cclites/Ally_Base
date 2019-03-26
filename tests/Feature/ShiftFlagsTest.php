<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\Shift;
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

class ShiftFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected $caregiver;
    protected $client;
    protected $service;

    protected function setUp()
    {
        parent::setUp();

        $this->client = factory(Client::class)->create(['max_weekly_hours' => 999.0]);
        $this->caregiver = factory(Caregiver::class)->create();

        $this->business = $this->client->business;
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->caregivers()->save($this->caregiver);
        $this->business->chain->caregivers()->save($this->caregiver);
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);

        $this->service = factory(Service::class)->create(['chain_id' => $this->client->business->businessChain->id, 'default' => true]);
        factory(ClientRate::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
        ]);
    }

    /**
     * @param $in
     * @param $out
     * @return Shift
     */
    protected function createDuplicateShift($in, $out)
    {
        if (strlen($in) === 8) $in = date('Y-m-d') . ' ' . $in;
        if (strlen($out) === 8) $out = date('Y-m-d') . ' ' . $out;
        return factory(Shift::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'checked_in_time' => $in,
            'checked_out_time' => $out,
            'status' => Shift::CLOCKED_IN,
            'service_id' => $this->service->id,
        ]);
    }

    /**
     * @param \Carbon\Carbon $date
     * @param string $in
     * @param string $out
     * @return Shift
     */
    public function createServiceBreakoutShift(Carbon $date, string $in, int $services, int $hoursPerService) : Shift
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
     * @return array
     */
    protected function makeShift(Carbon $date, string $in, string $out) : array
    {
        if (strlen($in) === 8) $in = $date->format('Y-m-d') . ' ' . $in;
        if (strlen($out) === 8) $out = $date->format('Y-m-d') . ' ' . $out;
        
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
     * @test
     */
    public function shifts_at_different_times_should_not_have_a_duplicate_flag()
    {
        $shift1 = $this->createDuplicateShift('12:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('02:00:00', '08:00:00');

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();

        $this->assertFalse($shift1->fresh()->hasFlag('duplicate'), 'The original shift should not have a duplicate flag');
        $this->assertFalse($shift2->hasFlag('duplicate'), 'The new shift should not have a duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_an_exact_matching_clock()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('10:00:00', '18:00:00');

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        
        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->fresh()->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_when_clocking_inside_of_the_hours()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('11:00:00', '16:00:00');

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        
        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->fresh()->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_when_clocking_outside_of_the_hours()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('08:00:00', '20:00:00'); // clock in is before, clock out is after, the reverse of inside

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        
        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->fresh()->hasFlag('duplicate'), 'The new shift did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_is_duplicated_by_another_shift_with_one_inside_and_one_outside()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('08:00:00', '16:00:00'); // clock in outside, clock out inside
        $shift3 = $this->createDuplicateShift('12:00:00', '20:00:00'); // clock in inside, clock out outside

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        $shift3->flagManager()->generate();
        
        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'), 'The original shift did not get the duplicate flag');
        $this->assertTrue($shift2->fresh()->hasFlag('duplicate'), 'The second shift (clock in outside) did not get the duplicate flag');
        $this->assertTrue($shift3->fresh()->hasFlag('duplicate'), 'The third shift (clock out outside) did not get the duplicate flag');
    }

    /**
     * @test
     */
    public function a_shift_touching_but_not_crossing_the_edge_does_not_cause_duplicate_flags()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('18:00:00', '19:00:00');
        $shift3 = $this->createDuplicateShift('06:00:00', '10:00:00');

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        $shift3->flagManager()->generate();
        
        $this->assertFalse($shift1->fresh()->hasFlag('duplicate'), 'The original shift incorrectly has a duplicate flag');
        $this->assertFalse($shift2->fresh()->hasFlag('duplicate'), 'The shift touching the clock out time incorrectly has a duplicate flag');
        $this->assertFalse($shift3->fresh()->hasFlag('duplicate'), 'The shift touching the clock in time incorrectly has a duplicate flag');
    }

    /**
     * @test
     */
    public function if_a_duplicate_shift_is_deleted_it_should_update_the_flags_of_its_duplicates()
    {
        $shift1 = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift2 = $this->createDuplicateShift('10:00:00', '18:00:00');

        // make sure shift is not readonly
        $shift1->update(['status' => Shift::WAITING_FOR_CONFIRMATION]);
        $shift2->update(['status' => Shift::WAITING_FOR_CONFIRMATION]);

        $shift1->flagManager()->generate();
        $shift2->flagManager()->generate();
        
        $this->assertTrue($shift1->fresh()->hasFlag('duplicate'));
        $this->assertTrue($shift2->fresh()->hasFlag('duplicate'));

        $this->business = $this->client->business;
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
        $this->actingAs($this->officeUser->user);

        $this->deleteJson(route('business.shifts.destroy', ['shift' => $shift1]))
            ->assertStatus(200);

        $this->assertEquals(null, $shift1->fresh());
        $this->assertFalse($shift2->fresh()->hasFlag('duplicate'));
    }

    /**
     * @test
     */
    public function flags_should_not_process_if_the_shift_is_currently_clocked_in()
    {
        $shift = $this->createDuplicateShift('10:00:00', '18:00:00');
        $shift->update(['checked_in_method' => Shift::METHOD_OFFICE]);
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::ADDED));

        $shift->syncFlags([]);
        $this->assertFalse($shift->fresh()->hasFlag(ShiftFlag::ADDED));

        $shift->statusManager()->update(Shift::CLOCKED_IN, ['checked_out_time' => null]);
        $shift->fresh()->flagManager()->generate();
        $this->assertFalse($shift->fresh()->hasFlag(ShiftFlag::ADDED));
    }

    ///////////////////////////////////////////
    /// OUTSIDE_AUTH flag tests
    ///////////////////////////////////////////

    /** @test */
    public function a_shift_should_be_flagged_when_actual_hours_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);
        $this->assertEquals(10, $this->client->fresh()->max_weekly_hours);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_be_flagged_when_service_breakout_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);
        $this->assertEquals(10, $this->client->fresh()->max_weekly_hours);

        $data = $this->makeShift(Carbon::now(), '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $shift2 = $this->createServiceBreakoutShift(Carbon::now(), '12:00:00', 3, 2);
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_be_flagged_when_actual_hours_shift_exceeds_weekly_service_auth_limits()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_be_flagged_when_actual_hours_shift_exceeds_monthly_service_auth_limits()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_be_flagged_when_actual_hours_shift_exceeds_daily_service_auth_limits()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now(), '10:00:00', '15:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_not_calculate_flags_on_shifts_outside_the_service_auth_period()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now()->startOfWeek()->subDays(3), '12:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_service_breakout_shift_can_flag_based_on_any_service_type()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $shift2 = $this->createServiceBreakoutShift(Carbon::now(), '12:00:00', 3, 2);
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_service_breakout_shift_can_flag_based_on_a_specific_service_type()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

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
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_service_breakout_shift_can_flag_based_on_a_specific_payer()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

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
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function fixed_hour_shifts_should_flag_if_exceeding_service_auth_fixed_limit()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::today(), '03:01:00', '04:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::today(), '04:01:00', '05:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null, 'fixed_rates' => 1]));
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function shifts_should_only_be_flagged_by_client_auths_effective_during_the_time_of_the_shift()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift(Carbon::today()->subMonths(2), '11:00:00', '18:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $this->assertCount(1, $shift->getActiveServiceAuths());
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_calculate_outside_auth_flags_based_on_the_period_the_shift_takes_place()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->fresh()->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift($date->addDays(2), '12:00:00', '18:00:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->fresh()->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function a_shift_should_exceed_clients_max_weekly_hours_based_on_the_period_the_shift_takes_place()
    {
        $this->client->update(['max_weekly_hours' => 10]);
        $this->assertEquals(10, $this->client->fresh()->max_weekly_hours);

        $date = Carbon::now()->subMonth(1)->startOfWeek();
        $data = $this->makeShift($date, '11:00:00', '18:00:00');
        $shift = Shift::create($data);
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $data = $this->makeShift($date, '12:00:00', '18:00:00');
        $shift2 = Shift::create($data);
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    public function an_actual_hours_shift_should_be_flagged_when_hours_exceed_full_term_limits()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

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
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        // shift should exceed the max unit but is outside the term dates
        $data = $this->makeShift(Carbon::now()->addYears(2), '10:00:00', '12:30:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        // shift exceeds the max units and is inside the term dates - flag
        $data = $this->makeShift(Carbon::now()->addMonths(2), '10:00:00', '12:30:00');
        $shift2 = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift2->flagManager()->generate();
        $this->assertTrue($shift2->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }

    /** @test */
    function an_actual_hours_shift_should_be_flagged_when_hours_exceeds_specific_daily_limits()
    {
        $this->assertEquals(999, $this->client->fresh()->max_weekly_hours);

        $auth1 = factory(ClientAuthorization::class)->create([
            'client_id' => $this->client->id,
            'service_id' => $this->service->id,
            'payer_id' => null,
            'units' => 0.0,
            'unit_type' => ClientAuthorization::UNIT_TYPE_HOURLY,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 5,
            'tuesday' => null,
        ]);

        // 4 hours shift on a monday -> no flag yet
        $data = $this->makeShift(Carbon::parse('last monday'), '10:00:00', '14:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        // 2 hour shift on any other day -> still no flag
        $data = $this->makeShift(Carbon::parse('last tuesday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift->flagManager()->generate();
        $this->assertFalse($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        // 2 hours shift on the same monday -> should flag
        $data = $this->makeShift(Carbon::parse('last monday'), '14:01:00', '16:00:00');
        $shift = Shift::create(array_merge($data, ['payer_id' => null]));
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }
}
