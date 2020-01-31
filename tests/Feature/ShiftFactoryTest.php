<?php

namespace Tests\Feature;

use App\Address;
use App\Billing\ClientRate;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ScheduleService;
use App\Business;
use App\Caregiver;
use App\Client;
use App\QuickbooksService;
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
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\ClientPayer;
use App\Billing\Payer;

class ShiftFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Business */
    private $business;
    /** @var \App\Client */
    private $client;
    /** @var \App\Caregiver */
    private $caregiver;

    public function setUp() : void
    {
        parent::setUp();
        $this->business = factory(Business::class)->create([
            'timezone' => 'UTC',
            'ot_behavior' => null,
            'ot_multiplier' => 1.5,
            'hol_behavior' => null,
            'hol_multiplier' => 1.5,
        ]);
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
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
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
        $this->business->update(['ot_behavior' => 'caregiver']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(22.50, $shift->caregiver_rate);
        $this->assertEquals(37.87, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtime_rates_for_providers()
    {
        $this->business->update(['ot_behavior' => 'provider']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(15.0, $shift->caregiver_rate);
        $this->assertEquals(37.12, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtime_rates_for_both_caregivers_and_providers()
    {
        $this->business->update(['ot_behavior' => 'both']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'overtime',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(22.5, $shift->caregiver_rate);
        $this->assertEquals(44.99, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtimes_rates_for_fixed_rate_shfits()
    {
        $this->business->update(['ot_behavior' => 'both']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'overtime',
            'fixed_rates' => true,
        ]);
        $this->assertTrue($schedule->fixed_rates);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('overtime', $shift->hours_type);
        $this->assertEquals(225.00, $shift->caregiver_rate);
        $this->assertEquals(449.99, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_holiday_rates()
    {
        $this->business->update(['hol_behavior' => 'both']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'holiday',
        ]);

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));

        $shift = $factory->create();
        $this->assertEquals('holiday', $shift->hours_type);
        $this->assertEquals(22.5, $shift->caregiver_rate);
        $this->assertEquals(44.99, $shift->client_rate);
    }

    /** @test */
    public function it_calculates_overtimes_rates_based_on_the_payer_ally_percentage()
    {
        $this->business->update(['ot_behavior' => 'caregiver']);
        $rate = $this->createDefaultClientRate();
        $schedule = $this->createDefaultRatesSchedule([
            'hours_type' => 'overtime',
        ]);

        // ally percentage defaults to the credit card value
        $this->assertEquals(0.05, $this->client->getAllyPercentage());
        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));
        $shift = $factory->create();
        $this->assertEquals(37.87, $shift->client_rate);

        // test ACH payment method
        $payer = factory(ClientPayer::class)->create(['client_id' => $this->client->id, 'payer_id' => Payer::PRIVATE_PAY_ID]);
        $this->client->setPaymentMethod(factory(BankAccount::class)->create());
        $this->assertEquals(0.03, $this->client->fresh()->getAllyPercentage());

        $factory = ShiftFactory::withSchedule($schedule, new ClockData(Shift::METHOD_GEOLOCATION));
        $shift = $factory->create();
        $this->assertEquals(37.73, $shift->client_rate);
    }

    public function createDefaultRatesSchedule($attributes = [])
    {
        return factory(Schedule::class)->create($attributes + [
            'client_id'  => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'caregiver_rate' => null,
            'client_rate' => null,
            'fixed_rates' => false,
        ]);
    }

    public function createDefaultClientRate()
    {
        return ClientRate::create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'client_hourly_rate' => 30.00,
            'caregiver_hourly_rate' => 15.00,
            'client_fixed_rate' => 300,
            'caregiver_fixed_rate' => 150,
            'service_id' => null,
            'payer_id' => null,
            'effective_start' => '2019-01-01',
            'effective_end' => '9999-12-31',
        ]);
    }

    /** @test */
    function a_shift_created_from_schedule_should_set_the_quickbooks_service_mapping()
    {
        $qbService = factory(QuickbooksService::class)->create();

        $schedule = factory(Schedule::class)->create([
            'quickbooks_service_id' => $qbService->id,
        ]);

        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();
        $this->assertEquals($qbService->id, $shift->quickbooks_service_id);
    }

    /** @test */
    function a_breakout_shift_created_from_schedule_should_set_all_the_quickbooks_service_mappings()
    {
        $qbService = factory(QuickbooksService::class)->create();

        $schedule = factory(Schedule::class)->create([
            'service_id' => null,
            'quickbooks_service_id' => null,
        ]);
        factory(ScheduleService::class, 2)->create(['schedule_id' => $schedule->id, 'quickbooks_service_id' => $qbService->id]);

        $factory = ShiftFactory::withSchedule(
            $schedule,
            new ClockData(Shift::METHOD_GEOLOCATION)
        );

        $shift = $factory->create();
        $this->assertCount(2, $shift->services);
        $this->assertEquals($qbService->id, $shift->services[0]->quickbooks_service_id);
        $this->assertEquals($qbService->id, $shift->services[1]->quickbooks_service_id);
    }

    /** @test */
    function a_shift_created_without_a_schedule_should_set_the_quickbooks_service_mapping()
    {
        $qbService = factory(QuickbooksService::class)->create();

        $factory = ShiftFactory::withoutSchedule(
            $this->client,
            $this->caregiver,
            new ClockData(Shift::METHOD_GEOLOCATION),
            null,
            null,
            null,
            null,
            null,
            $qbService->id
        );

        $shift = $factory->create();
        $this->assertEquals($qbService->id, $shift->quickbooks_service_id);
    }

    /** @test */
    function a_breakout_shift_created_without_a_schedule_should_set_all_the_quickbooks_service_mappings()
    {
        $qbService = factory(QuickbooksService::class)->create();

        $services = factory(ShiftService::class, 2)->create(['quickbooks_service_id' => $qbService->id]);

        $factory = ShiftFactory::withoutSchedule(
            $this->client,
            $this->caregiver,
            new ClockData(Shift::METHOD_GEOLOCATION)
        )->withServices($services->toArray());

        $shift = $factory->create();
        $this->assertCount(2, $shift->services);
        $this->assertEquals($qbService->id, $shift->services[0]->quickbooks_service_id);
        $this->assertEquals($qbService->id, $shift->services[1]->quickbooks_service_id);
    }
}
