<?php

namespace Tests\Feature;

use App\Address;
use App\Billing\ClientRate;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Schedule;
use App\Shift;
use App\Shifts\Data\CaregiverClockoutData;
use App\Shifts\Data\ClockData;
use App\Shifts\Data\EVVData;
use App\Shifts\EVVClockInData;
use App\Shifts\ShiftFactory;
use Carbon\Carbon;
use Packages\GMaps\GeocodeCoordinates;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShiftFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Business */
    private $business;
    /** @var \App\Client */
    private $client;
    /** @var \App\Caregiver */
    private $caregiver;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create(['timezone' => 'UTC']);
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
    }


    /**
     * @test
     */
    function a_shift_can_be_created_using_the_factory_with_minimum_data()
    {
        $factory = ShiftFactory::withoutSchedule(
            $this->client,
            $this->caregiver,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();
        $this->assertInstanceOf(Shift::class, $shift, "The withoutSchedule shift data did not create a Shift instance.");
        $this->assertGreaterThan(0, $shift->id, "The withoutSchedule shift data did not get persisted to the database.");
    }

    /**
     * @test
     */
    function a_shift_can_be_created_from_a_schedule()
    {
        $schedule = factory(Schedule::class)->create([
            'caregiver_rate' => 15,
            'client_rate' => 20,
        ]);

        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();
        $this->assertInstanceOf(Shift::class, $shift, "The withSchedule shift data did not create a Shift instance.");
        $this->assertGreaterThan(0, $shift->id, "The withSchedule shift data did not get persisted to the database.");
        $this->assertEquals($schedule->id, $shift->schedule_id, "The withSchedule shift data did not persist the schedule_id.");
        $this->assertEquals(20, $shift->client_rate, "The withSchedule shift data did not accept the client rate.");
        $this->assertEquals(15, $shift->caregiver_rate, "The withSchedule shift data did not accept the caregiver rate.");
    }

    /**
     * @test
     */
    function a_shift_can_be_created_with_additional_data_classes()
    {
        $schedule = factory(Schedule::class)->create();
        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $address = factory(Address::class)->create();
        $coordinates = new GeocodeCoordinates(50.0, -50.0);
        $clockInData = new EVVClockInData(new EVVData($address, $coordinates, true, null, '127.0.0.1', 'Some User Agent String'));
        $clockOutData = new CaregiverClockoutData(new ClockData(Shift::METHOD_GEOLOCATION), 1.0, 0.0);

        $shift = $factory->create($clockInData, $clockOutData);
        $this->assertInstanceOf(Shift::class, $shift, "The additional shift data did not create a Shift instance.");
        $this->assertGreaterThan(0, $shift->id, "The additional shift data did not get persisted to the database.");
        $this->assertEquals($coordinates->latitude, $shift->checked_in_latitude, 'The additional clock in data did not get added to the shift instance.');
        $this->assertEquals(1.0, $shift->mileage, 'The additional clock out data did not get added to the shift instance.');
    }

    /**
     * @test
     */
    function a_shift_pulls_in_the_rate_from_client_rates_when_rates_are_null()
    {
        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 22.50,
            'caregiver_hourly_rate' => 16.25,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $factory = ShiftFactory::withoutSchedule(
            $this->client,
            $this->caregiver,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();

        $this->assertEquals(22.50, $shift->client_rate, "The shift data did not pull in the client rate.");
        $this->assertEquals(16.25, $shift->caregiver_rate, "The shift data did not pull in the caregiver rate");
    }

    /**
     * @test
     */
    function a_shift_pulls_in_the_rate_from_client_rates_when_SCHEDULE_rates_are_null()
    {
        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 22.50,
            'caregiver_hourly_rate' => 16.25,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
        ]);

        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();

        $this->assertEquals(22.50, $shift->client_rate, "The shift data did not pull in the client rate.");
        $this->assertEquals(16.25, $shift->caregiver_rate, "The shift data did not pull in the caregiver rate");
    }

    /**
     * Make sure the timezone is not mutated during creation
     *
     * @test
     */
    function shift_factory_produces_UTC_timestamps()
    {
        $this->client->business->update(['timezone' => 'America/New_York']);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
        ]);

        $now = Carbon::now('UTC');
        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION),
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();

        $this->assertNotEquals('UTC', $this->client->getTimezone(),  'For this test to be accurate, the client timezone should not be UTC.');
        $this->assertEquals('UTC', $factory->toArray()['checked_in_time']->getTimezone()->getName());
        $this->assertEquals('UTC', $factory->toArray()['checked_out_time']->getTimezone()->getName());
        $this->assertEquals('UTC', $shift->checked_in_time->getTimezone()->getName());
        $this->assertEquals('UTC', $shift->checked_out_time->getTimezone()->getName());
        $this->assertEquals($now->toDateTimeString(), $shift->checked_in_time->toDateTimeString());
        $this->assertEquals($now->toDateTimeString(), $shift->checked_out_time->toDateTimeString());
    }

    /** @test */
    public function it_does_not_calculate_overtime_rates_when_behavior_setting_is_turned_off()
    {
        $this->client->business->update([
            'ot_behavior' => null,
            'hol_behavior' => null,
        ]);

        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 30.00,
            'caregiver_hourly_rate' => 15.00,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(15.00, $shift->caregiver_rate);
        $this->assertEquals(30.00, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtime_rates_for_caregivers()
    {
        $this->client->business->update([
            'ot_behavior' => 'caregiver',
            'ot_multiplier' => 1.5,
            'rate_structure' => 'client_rate',
        ]);

        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 30.00,
            'caregiver_hourly_rate' => 15.00,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(22.50, $shift->caregiver_rate);
        $this->assertEquals(37.80, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtime_rates_for_providers()
    {
        $this->client->business->update([
            'ot_behavior' => 'provider',
            'ot_multiplier' => 1.5,
            'rate_structure' => 'client_rate',
        ]);

        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 30.00,
            'caregiver_hourly_rate' => 15.00,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(15.0, $shift->caregiver_rate);
        // provider rate = 30 - 15 - 1.5 = 13.5
        // 1.5x provider rate = 20.25
        // new charge = 15 + 20.25 = 35.25 (+1.7625 ally fee)
        $this->assertEquals(37.01, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtime_rates_for_both_caregivers_and_providers()
    {
        $this->client->business->update([
            'ot_behavior' => 'both',
            'ot_multiplier' => 1.5,
            'rate_structure' => 'client_rate',
        ]);

        $rate = ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 30.00,
            'caregiver_hourly_rate' => 15.00,
            'client_fixed_rate' => 0, // unused
            'caregiver_fixed_rate' => 0, // unused
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);

        $schedule = factory(Schedule::class)->create([
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(22.5, $shift->caregiver_rate);
        $this->assertEquals(44.89, $shift->client_rate);
    }
}
