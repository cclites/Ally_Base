<?php

namespace Tests\Feature;

use App\Billing\ClientPayer;
use App\Billing\ClientRate;
use App\Billing\Payer;
use App\Billing\Service;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use Tests\CreatesBusinesses;
use Tests\CreatesSchedules;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignCaregiverFromScheduleTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesSchedules;

    public function setUp()
    {
        parent::setUp();

        $this->disableExceptionHandling();
//        $this->withExceptionHandling();

        $this->createBusinessWithUsers();

        // make sure there are no client rates
        ClientRate::whereRaw(1)->delete();
        $this->assertCount(0, ClientRate::all());

        // make sure caregiver is not assigned to the client
        \DB::table('client_caregivers')->delete();

        // create a default service
        $this->service = factory(Service::class)->create(['name' => 'General', 'chain_id' => $this->chain->id, 'default' => true]);
    }

    public function submitCreateForm(array $data)
    {
        // Ensure all caregiver relationships are emptied.
        // This is a fix because the Schedule factory creates this relationship.
        $this->client->caregivers()->detach();
        $this->assertCount(0, $this->client->caregivers->fresh());

        return $this->post(route('business.schedule.store'), $data);
    }

    public function submitUpdateForm(Schedule $schedule, array $data)
    {
        return $this->patch(route('business.schedule.update', ['schedule' => $schedule->id]), $data);
    }

    /** @test */
    function it_should_create_and_use_a_default_rate_when_creating_a_hourly_rate_schedule()
    {
        $this->actingAs($this->officeUser->user);

        $data = $this->makeSchedule(Carbon::today(), '03:00:00', 4, [
            'fixed_rates' => 0,
            'caregiver_rate' => 15.00,
            'client_rate' => 20.00,
        ]);

        $this->submitCreateForm($data)
            ->assertStatus(201);

        $this->assertCount(1, Schedule::all());
        $schedule = Schedule::first();

        $this->assertCount(1, ClientRate::all());
        $newRate = ClientRate::first();
        $this->assertEquals($data['caregiver_rate'], $newRate->caregiver_hourly_rate);
        $this->assertEquals($data['client_rate'], $newRate->client_hourly_rate);
        $this->assertEquals(null, $schedule->getRates()->clientRate());
        $this->assertEquals(null, $schedule->getRates()->caregiverRate());
        $this->assertEquals(false, $schedule->getRates()->fixedRates());
    }

    /** @test */
    function it_should_match_the_payer_id_when_creating_rates()
    {
        $this->actingAs($this->officeUser->user);

        $clientPayer = factory(ClientPayer::class)->create();
        $data = $this->makeSchedule(Carbon::today(), '03:00:00', 4, [
            'fixed_rates' => 0,
            'caregiver_rate' => 15.00,
            'client_rate' => 20.00,
            'payer_id' => $clientPayer->payer->id,
        ]);
        $this->submitCreateForm($data)
            ->assertStatus(201);

        $this->assertCount(1, Schedule::all());
        $schedule = Schedule::first();
        $this->assertEquals($this->service->id, $schedule->service_id);
        $this->assertEquals($clientPayer->payer->id, $schedule->payer_id);

        $this->assertCount(1, ClientRate::all());
        $newRate = ClientRate::first();
        $this->assertEquals($data['payer_id'], $newRate->payer_id);
    }

    /** @test */
    function it_should_create_a_default_rate_for_all_services_when_there_is_only_one_service()
    {
        $this->actingAs($this->officeUser->user);

        $clientPayer = factory(ClientPayer::class)->create();
        $data = $this->makeSchedule(Carbon::today(), '03:00:00', 4, [
            'fixed_rates' => 0,
            'caregiver_rate' => 15.00,
            'client_rate' => 20.00,
            'payer_id' => $clientPayer->payer->id,
        ]);
        $this->submitCreateForm($data)
            ->assertStatus(201);

        $this->assertCount(1, ClientRate::all());
        $newRate = ClientRate::first();
        $this->assertEquals(null, $newRate->service_id);
    }

    /** @test */
    function it_should_create_and_use_a_default_rate_when_creating_a_fixed_rate_schedule()
    {
        $this->actingAs($this->officeUser->user);

        $data = $this->makeSchedule(Carbon::today(), '03:00:00', 4, [
            'fixed_rates' => 1, // FIXED
            'caregiver_rate' => 15.00,
            'client_rate' => 20.00,
        ]);
        $this->submitCreateForm($data)
            ->assertStatus(201);

        $this->assertCount(1, Schedule::all());
        $schedule = Schedule::first();
        $this->assertEquals($this->service->id, $schedule->service_id);
        $this->assertEquals(Payer::PRIVATE_PAY_ID, $schedule->payer_id);

        $this->assertCount(1, ClientRate::all());
        $newRate = ClientRate::first();
        $this->assertEquals($data['caregiver_rate'], $newRate->caregiver_fixed_rate);
        $this->assertEquals($data['client_rate'], $newRate->client_fixed_rate);
        $this->assertEquals(null, $schedule->getRates()->clientRate());
        $this->assertEquals(null, $schedule->getRates()->caregiverRate());
        $this->assertEquals(true, $schedule->getRates()->fixedRates());
    }

    /** @test */
    function it_should_create_and_use_default_rates_when_creating_a_service_breakout_schedule()
    {
        $this->actingAs($this->officeUser->user);
        $payer = factory(Payer::class)->create([]);
        $clientPayer = factory(ClientPayer::class)->create(['payer_id' => $payer]);

        $service2 = factory(Service::class)->create(['name' => 'Another Service', 'chain_id' => $this->chain->id, 'default' => false]);
        $service3 = factory(Service::class)->create(['name' => 'More Services', 'chain_id' => $this->chain->id, 'default' => false]);

        $data = $this->makeServiceBreakoutSchedule(Carbon::today(), '03:00:00', [$this->service->id, $service2->id, $service3->id], 1);
        $data['services'][0]['payer_id'] = null;
        $data['services'][0]['client_rate'] = 20;
        $data['services'][0]['caregiver_rate'] = 15;
        $data['services'][1]['client_rate'] = 30;
        $data['services'][1]['caregiver_rate'] = 25;
        $data['services'][2]['payer_id'] = null;
        $data['services'][2]['client_rate'] = 40;
        $data['services'][2]['caregiver_rate'] = 35;
        $data['services'][2]['payer_id'] = $clientPayer->payer->id; // test different payer

        $this->submitCreateForm($data)
            ->assertStatus(201);

        $this->assertCount(1, Schedule::all());
        $schedule = Schedule::first();
        $this->assertEquals(null, $schedule->service_id);
        $this->assertEquals(null, $schedule->payer_id);
        $this->assertEquals(null, $schedule->client_rate);

        // all rates should be set to use defaults
        $this->assertEquals(null, $schedule->services[0]->client_rate);
        $this->assertEquals(null, $schedule->services[1]->client_rate);
        $this->assertEquals(null, $schedule->services[2]->client_rate);

        $this->assertCount(3, ClientRate::all());
        $rate1 = ClientRate::where('service_id', $data['services'][0]['service_id'])->first();
        $this->assertEquals($data['services'][0]['client_rate'], $rate1->client_hourly_rate);
        $this->assertEquals($data['services'][0]['caregiver_rate'], $rate1->caregiver_hourly_rate);

        $rate2 = ClientRate::where('service_id', $data['services'][1]['service_id'])->first();
        $this->assertEquals($data['services'][1]['client_rate'], $rate2->client_hourly_rate);
        $this->assertEquals($data['services'][1]['caregiver_rate'], $rate2->caregiver_hourly_rate);
        $this->assertNotEquals($clientPayer->payer->id, $rate2->payer_id);

        $rate3 = ClientRate::where('service_id', $data['services'][2]['service_id'])->first();
        $this->assertEquals($data['services'][2]['client_rate'], $rate3->client_hourly_rate);
        $this->assertEquals($data['services'][2]['caregiver_rate'], $rate3->caregiver_hourly_rate);
        $this->assertEquals($data['services'][2]['payer_id'], $rate3->payer_id);
    }

    /** @test */
    function it_should_not_add_duplicate_rates_per_service_id()
    {
        $this->actingAs($this->officeUser->user);

        $data = $this->makeServiceBreakoutSchedule(Carbon::today(), '03:00:00', [$this->service->id, $this->service->id], 1);
        $data['services'][0]['client_rate'] = 20;
        $data['services'][0]['caregiver_rate'] = 15;
        $data['services'][1]['client_rate'] = 30;
        $data['services'][1]['caregiver_rate'] = 25;

        $this->submitCreateForm($data)
            ->assertStatus(500)
            ->assertSee('because you have different rates for the same service');

        $this->assertCount(0, Schedule::all());
        $this->assertCount(0, ClientRate::all());
    }

    /** @test */
    function it_should_not_save_default_rates_if_using_overtime_or_holiday_rates()
    {
        $this->actingAs($this->officeUser->user);

        // NORMAL
        $data = $this->makeSchedule(Carbon::today(), '03:00:00', 4, [
            'hours_type' => Shift::HOURS_OVERTIME,
        ]);

        $this->submitCreateForm($data)
            ->assertStatus(500)
            ->assertSee('because you are using HOL\/OT rates');

        $this->assertCount(0, Schedule::all());
        $this->assertCount(0, ClientRate::all());

        // BREAKOUT
        $data = $this->makeServiceBreakoutSchedule(Carbon::today(), '03:00:00', [$this->service->id], 1);
        $data['services'][0]['hours_type'] = Shift::HOURS_OVERTIME;

        $this->submitCreateForm($data)
            ->assertStatus(500)
            ->assertSee('because you are using HOL\/OT rates');

        $this->assertCount(0, Schedule::all());
        $this->assertCount(0, ClientRate::all());
    }

    /** @test */
    function it_should_create_and_use_a_default_rate_when_updating_a_hourly_rate_schedule()
    {
        $this->actingAs($this->officeUser->user);

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4, [
            'fixed_rates' => 0,
            'caregiver_rate' => 15.00,
            'client_rate' => 20.00,
        ]);

        $newCaregiver = factory('App\Caregiver')->create();
        $this->chain->assignCaregiver($newCaregiver);

        $data = array_merge($schedule->toArray(), ['caregiver_id' => $newCaregiver->id]);

        $this->submitUpdateForm($schedule, $data)
            ->assertStatus(200);

        $this->assertCount(1, Schedule::all());
        $schedule = Schedule::first();

        $this->assertCount(1, ClientRate::all());
        $newRate = ClientRate::first();
        $this->assertEquals($data['caregiver_rate'], $newRate->caregiver_hourly_rate);
        $this->assertEquals($data['client_rate'], $newRate->client_hourly_rate);
        $this->assertEquals(null, $schedule->getRates()->clientRate());
        $this->assertEquals(null, $schedule->getRates()->caregiverRate());
        $this->assertEquals(false, $schedule->getRates()->fixedRates());
    }
}
