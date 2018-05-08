<?php
namespace Tests\Feature;

use App\Address;
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

    public $business;
    public $client;
    public $caregiver;
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
        $shift = $clockIn->setManual()->clockIn($this->schedule);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertEquals(Shift::CLOCKED_IN, $shift->status);
        $this->assertTrue($shift->checked_in);
        $this->assertLessThan(3, $shift->checked_in_time->diffInSeconds(Carbon::now()));
        $this->assertNull($shift->checked_out_time);
        $this->assertFalse($shift->isVerified());
    }

    public function test_a_schedule_can_be_clocked_in_to_with_a_client_number()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockIn($this->schedule);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertTrue($shift->isVerified());
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
        $this->assertTrue($shift->isVerified());
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

    public function test_a_shift_can_be_clocked_in_to_without_a_schedule()
    {
        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setManual()->clockInWithoutSchedule($this->business, $this->client);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertFalse($shift->isVerified());
    }

    public function test_a_shift_can_be_clocked_in_to_without_a_schedule_by_phone()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockInWithoutSchedule($this->business, $this->client);

        $this->assertInstanceOf(Shift::class, $shift);
        $this->assertTrue($shift->isVerified());
        $this->assertTrue($shift->checked_in_verified);
    }
}