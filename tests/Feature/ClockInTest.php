<?php
namespace Tests\Feature;

use App\Address;
use App\Billing\ClientRate;
use App\Business;
use App\Caregiver;
use App\Client;
use App\PhoneNumber;
use App\Schedule;
use App\Shift;
use App\Shifts\ClockIn;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Business
     */
    public $business;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Caregiver
     */
    public $caregiver;

    /**
     * @var Schedule
     */
    public $schedule;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->schedule = factory(Schedule::class)->create([
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);

    }

    public function test_a_schedule_can_be_clocked_in_to()
    {
        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->clockIn($this->schedule);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertEquals(Shift::CLOCKED_IN, $shift->status);
        $this->assertLessThan(3, $shift->checked_in_time->diffInSeconds(Carbon::now()));
        $this->assertNull($shift->checked_out_time);
        $this->assertFalse($shift->isVerified());
    }

    public function test_checked_in_method_defaults_to_geolocation()
    {
        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->clockIn($this->schedule);

        $this->assertEquals(Shift::METHOD_GEOLOCATION, $shift->checked_in_method);
    }

    public function test_a_schedule_can_be_clocked_in_to_with_a_client_number()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockIn($this->schedule);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertTrue($shift->checked_in_verified);
        $this->assertEquals(Shift::METHOD_TELEPHONY, $shift->checked_in_method);
    }

    public function test_a_schedule_can_be_clocked_in_to_with_a_valid_geocode()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setGeocode($latitude, $longitude)->clockIn($this->schedule);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertTrue($shift->checked_in_verified);
    }

    public function test_a_shift_has_distance_and_verified_set_with_valid_geocode()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setGeocode($latitude, $longitude)->clockIn($this->schedule);

        $this->assertTrue($shift->checked_in_verified);
        $this->assertNotNull($shift->checked_in_distance);
    }

    public function test_an_unverified_shift_being_clocked_into_still_records_distance()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(\App\Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setGeocode($latitude + 1, $longitude + 1)
            ->clockIn($this->schedule);

        $this->assertEquals(false, $shift->checked_in_verified);
        $this->assertNotNull($shift->checked_in_distance);
    }

    public function test_a_shift_can_be_clocked_in_to_without_a_schedule()
    {
        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->clockInWithoutSchedule($this->client);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertFalse($shift->isVerified());
    }

    public function test_a_shift_can_be_clocked_in_to_without_a_schedule_by_phone()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockInWithoutSchedule($this->client);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertTrue($shift->checked_in_verified);
    }

    /** @test */
    function if_a_caregiver_with_only_fixed_rates_assigned_clocks_in_without_a_schedule_it_should_mark_the_shift_as_fixed()
    {
        ClientRate::whereRaw(1)->delete();

        $rate = factory(ClientRate::class)->create([
            'effective_start' => Carbon::yesterday()->format('Y-m-d'),
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'caregiver_hourly_rate' => 0.00,
            'caregiver_fixed_rate' => 50.00,
            'client_hourly_rate' => 0.00,
            'client_fixed_rate' => 60.00,
            'payer_id' => null,
            'service_id' => null,
        ]);

        $this->assertCount(1, ClientRate::all());

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->clockInWithoutSchedule($this->client);

        $this->assertTrue($shift->fixed_rates);
        $this->assertEquals(50, $shift->caregiver_rate);
        $this->assertEquals(60, $shift->client_rate);
    }
}