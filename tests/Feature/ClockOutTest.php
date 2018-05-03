<?php
namespace Tests\Feature;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Events\UnverifiedShiftLocation;
use App\Events\UnverifiedShiftCreated;
use App\PhoneNumber;
use App\Shift;
use App\ShiftIssue;
use App\Shifts\ClockOut;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public $business;
    public $client;
    public $caregiver;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
    }

    public function test_active_shift_can_be_clocked_out()
    {
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        $this->assertTrue($result);
        $this->assertFalse($shift->statusManager()->isClockedIn());
    }

    public function test_unverified_shift_sends_event()
    {
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);

        $this->expectsEvents(UnverifiedShiftCreated::class);
        $result = $clockOut->clockOut($shift);
    }

    public function test_verified_shift_with_phone_number()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        // Event should not go out
        $this->doesntExpectEvents(UnverifiedShiftCreated::class);

        $shift = $this->createShift(['verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertTrue($result);
        $this->assertTrue($shift->isVerified());
    }

    public function test_initially_verified_shift_is_unverified_when_clocking_out_manually()
    {
        $shift = $this->createShift(['verified' => true, 'checked_in_number' => 5555555555]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setManual()
                           ->clockOut($shift);

        $this->assertTrue($result);
        $this->assertFalse($shift->isVerified());
    }

    public function test_activities_can_be_attached_by_id()
    {
        $activities = factory(Activity::class, 3)->create();

        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->attachActivities($shift, $activities->pluck('id')->toArray());

        $this->assertTrue($result);
        $this->assertEquals(3, $shift->activities()->count());
    }

    public function test_shift_issue_model_can_be_attached()
    {
        $issue = factory(ShiftIssue::class)->make();

        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $clockOut->attachIssue($shift, $issue);

        $this->assertNotNull($issue->id);
        $this->assertEquals($issue->id, $shift->issues()->value('id'));
    }

    public function test_unverified_location_dispatches_event()
    {
        \Event::fake();
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        \Event::assertDispatched(UnverifiedShiftLocation::class, function ($e) use ($shift) {
            return $e->shift->id === $shift->id;
        });
    }

    public function test_using_telephony_number_does_not_dispatches_locations_event()
    {
        \Event::fake();
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $clockOut->setNumber(555555555);
        $result = $clockOut->clockOut($shift);

        \Event::assertNotDispatched(UnverifiedShiftLocation::class);
    }

    public function test_auto_confirm_does_create_unverified_exceptions()
    {
        $this->business = factory(Business::class)->create(['auto_confirm' => true]);
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        // Exception should not exist
        $this->assertEquals(1, $shift->exceptions()->count());
    }

    public function test_auto_confirm_creates_verified_shifts_waiting_for_authorization()
    {
        $this->business = factory(Business::class)->create(['auto_confirm' => true]);

        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $shift = $this->createShift(['verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertEquals(Shift::WAITING_FOR_AUTHORIZATION, $shift->status);
    }

    public function test_auto_confirm_disabled_does_not_create_exceptions()
    {
        $this->business = factory(Business::class)->create(['auto_confirm' => false]);
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        // Exception should not exist
        $this->assertEquals(0, $shift->exceptions()->count());
    }

    public function test_auto_confirm_disabled_creates_verified_shifts_waiting_for_confirmation()
    {
        $this->business = factory(Business::class)->create(['auto_confirm' => false]);

        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $shift = $this->createShift(['verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertEquals(Shift::WAITING_FOR_CONFIRMATION, $shift->status);
    }


    /**
     * @param array $attributes
     * @return \App\Shift
     */
    protected function createShift($attributes = []) {
        $attributes += [
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'checked_in' => true,
            'checked_in_time' => Carbon::now()->subHour(),
            'checked_in_number' => null,
            'checked_in_latitude' => null,
            'checked_in_longitude' => null,
            'checked_out_time' => null,
            'checked_out_number' => null,
            'checked_out_latitude' => null,
            'checked_out_longitude' => null,
            'status' => Shift::CLOCKED_IN,
        ];
        return factory(Shift::class)->create($attributes);
    }
}