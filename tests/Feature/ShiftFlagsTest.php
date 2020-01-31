<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\Shift;
use Tests\CreatesClientAuthorizations;
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
    use CreatesClientAuthorizations;

    protected $caregiver;
    protected $client;
    protected $service;

    protected function setUp() : void
    {
        parent::setUp();

        $this->client = factory(Client::class)->create(['max_weekly_hours' => 999.0]);
        $this->caregiver = factory(Caregiver::class)->create();

        $this->business = $this->client->business;
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->chain->assignCaregiver($this->caregiver);
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
     * @param null|\Carbon\Carbon $endDate
     * @return array
     */
    protected function makeShift(Carbon $date, string $in, string $out, ?Carbon $endDate = null) : array
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

    /** @test */
    function a_shift_that_fails_the_service_auth_validator_should_be_flagged_outside_auth()
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
    function a_shift_that_is_no_longer_outside_auth_should_remove_its_flag()
    {
        $this->client->update(['max_weekly_hours' => 999]);

        $auth = $this->createClientAuth([
            'units' => 1,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $data = $this->makeShift(Carbon::now(), '12:00:00', '18:00:00');
        $shift = Shift::create($data);
        $shift->flagManager()->generate();
        $this->assertTrue($shift->hasFlag(ShiftFlag::OUTSIDE_AUTH));

        $auth->update([
            'units' => 99,
        ]);

        $shift->fresh()->flagManager()->generate();
        $this->assertFalse($shift->fresh()->hasFlag(ShiftFlag::OUTSIDE_AUTH));
    }
}
