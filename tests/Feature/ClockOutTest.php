<?php
namespace Tests\Feature;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\ClientType;
use App\Events\UnverifiedShiftLocation;
use App\Events\UnverifiedClockOut;
use App\PhoneNumber;
use App\Shift;
use App\ShiftIssue;
use App\Shifts\ClockIn;
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

        $this->assertNull($shift->checked_out_time);

        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        $this->assertTrue($result);
        $this->assertFalse($shift->fresh()->statusManager()->isClockedIn());
        $this->assertNotNull($shift->fresh()->checked_out_time);
        $this->assertTrue($shift->fresh()->checked_out_time->isToday());
    }

    public function test_hours_are_set_once_clocked_out()
    {
        $shift = $this->createShift();

        $this->assertNull($shift->hours, 'The shift\'s hours should not be set prior to clock out.');
        $duration = $shift->duration();

        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        $this->assertEquals($duration, $shift->hours, 'The shift\'s hours should be set after clock out.');
    }

    public function test_unverified_shift_sends_event()
    {
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);

        $this->expectsEvents(UnverifiedClockOut::class);
        $result = $clockOut->clockOut($shift);
    }

    public function test_a_shift_is_unverified_until_clocked_out()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockInWithoutSchedule($this->client);
        $this->assertFalse($shift->isVerified());

        $clockOut = new ClockOut($this->caregiver);
        $clockOut->setNumber($phone->national_number)->clockOut($shift);
        $this->assertTrue($shift->isVerified());
    }

    public function test_verified_shift_with_phone_number()
    {
        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        // Event should not go out
        $this->doesntExpectEvents(UnverifiedClockOut::class);

        $shift = $this->createShift(['checked_in_verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertTrue($result);
        $this->assertTrue($shift->isVerified());
    }

    public function test_a_shift_has_distance_and_verified_set_with_valid_geocode()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(\App\Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $shift = $this->createShift(['checked_in_verified' => true]);
        $clockOut = new ClockOut($this->caregiver);
        $clockOut->setGeocode($latitude, $longitude)->clockOut($shift);

        $this->assertTrue($shift->checked_out_verified);
        $this->assertNotNull($shift->checked_out_distance);
    }

    public function test_an_unverified_shift_being_clocked_out_with_a_valid_location_still_sets_distance()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(\App\Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $shift = $this->createShift(['verified' => false]);
        $clockOut = new ClockOut($this->caregiver);
        $clockOut->setGeocode($latitude, $longitude)->clockOut($shift);

        $this->assertTrue($shift->checked_out_verified);
        $this->assertNotNull($shift->checked_out_distance);
    }

    public function test_an_unverified_location_still_records_distance()
    {
        // Make a client address
        $latitude = 45;
        $longitude = -80;
        $type = 'evv';
        $address = factory(\App\Address::class)->make(compact('type', 'latitude', 'longitude'));
        $this->client->addresses()->save($address);

        $shift = $this->createShift(['verified' => false]);
        $clockOut = new ClockOut($this->caregiver);
        $clockOut->setGeocode($latitude + 1, $longitude + 1)->clockOut($shift);

        $this->assertFalse($shift->checked_out_verified);
        $this->assertNotNull($shift->checked_out_distance);
    }

    public function test_initially_verified_shift_is_unverified_when_clocking_out_manually()
    {
        $shift = $this->createShift(['verified' => true, 'checked_in_number' => 5555555555]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

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
        $this->business = factory(Business::class)->create(['auto_confirm_verified_shifts' => true]);
        $shift = $this->createShift();
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->clockOut($shift);

        // Exception should not exist
        $this->assertEquals(0, $shift->systemNotifications()->count());
    }

    public function test_auto_confirm_creates_verified_shifts_waiting_for_authorization()
    {
        $this->business = factory(Business::class)->create(['auto_confirm_verified_shifts' => true]);

        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $shift = $this->createShift(['checked_in_verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertEquals(Shift::WAITING_FOR_AUTHORIZATION, $shift->status);
    }

    public function test_auto_confirm_disabled_creates_verified_shifts_waiting_for_confirmation()
    {
        $this->business = factory(Business::class)->create(['auto_confirm_verified_shifts' => false]);

        // Make a client phone number
        $phone = factory(PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        $shift = $this->createShift(['verified' => true, 'checked_in_number' => $phone->national_number]);
        $clockOut = new ClockOut($this->caregiver);
        $result = $clockOut->setNumber($phone->national_number)
                           ->clockOut($shift);

        $this->assertEquals(Shift::WAITING_FOR_CONFIRMATION, $shift->status);
    }

    /** @test */
    public function clocking_out_should_attach_all_custom_questions_to_the_shift()
    {
        $this->withoutExceptionHandling();
        
        $shift = $this->createShift();

        $this->actingAs($this->caregiver->user);

        $activities = $this->setActivities( $this->client->client_type );

        $question = factory(\App\Question::class)->create(['business_id' => $this->business->id, 'client_type' => $this->client->client_type]);

        $this->assertCount(1, $this->business->fresh()->questions()->forType($this->client->client_type)->get());

        $data = [
            'clientSignature' => 'test',
            'caregiver_comments' => 'test',
            'activities' => $activities,
            'questions' => [$question->id => 'answer'],
            'goals' => [],
        ];

        $this->postJson(route('clock_out.show', $shift), $data)
            ->assertStatus(200);

        $this->assertCount(1, $shift->fresh()->questions);
    }

    /** @test */
    public function clocking_out_should_throw_validation_errors_if_there_are_unanswered_custom_questions()
    {
        $shift = $this->createShift();

        $this->actingAs($this->caregiver->user);

        $activities = $this->setActivities( $this->client->client_type );

        $question = factory(\App\Question::class)->create(['required' => 1, 'business_id' => $this->business->id, 'client_type' => $this->client->client_type]);

        $data = [
            'clientSignature' => 'test',
            'caregiver_comments' => 'test',
            'activities' => $activities,
            'questions' => [$question->id => ''],
            'goals' => [],
        ];

        $this->postJson(route('clock_out.show', $shift), $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('questions.1');
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
            'checked_in_method' => Shift::METHOD_GEOLOCATION,
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

    protected function setActivities( $client_type )
    {

        $activity   = factory(Activity::class)->create();
        $activities = [ $activity->id ];

        if( in_array( $client_type, [ ClientType::LTCI, ClientType::MEDICAID ] ) ){
            // technically just testing for the 2nd activity requirement if the factory builds with this client_type

            $activity2 = factory(Activity::class)->create();
            $activities = [$activity->id, $activity2->id];
        }

        return $activities;
    }
}
