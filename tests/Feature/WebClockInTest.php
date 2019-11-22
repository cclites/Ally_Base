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
use App\Shifts\ClockOut;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesBusinesses;
use Tests\CreatesSchedules;
use Tests\CreatesShifts;
use Tests\TestCase;

class WebClockInTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBusinesses;
    use CreatesSchedules;
    use CreatesShifts;

    public function setUp()
    {
        parent::setUp();
        $this->createBusinessWithUsers(true);
        $this->service = $this->createDefaultService($this->chain);

        // Set current time to today at noon in the current businesses's timezone
        Carbon::setTestNow(Carbon::parse(Carbon::now()->format('Y-m-d').' 12:00:00', $this->business->getTimezone()));

        $this->actingAs($this->caregiver->user);
    }

    /** @test */
    function a_caregiver_can_only_see_schedules_to_clock_in_that_are_within_two_hours_before_or_after_the_current_time()
    {
        $s1 = $this->createSchedule(Carbon::now(), '09:02:00', 1);
        $s2 = $this->createSchedule(Carbon::now(), '13:58:00', 1);

        $s3 = $this->createSchedule(Carbon::now(), '08:58:00', 1);
        $s4 = $this->createSchedule(Carbon::now(), '14:02:00', 1);

        $this->get("/caregiver/schedules/{$this->client->id}")
            ->assertJsonFragment(['id' => $s1->id])
            ->assertJsonFragment(['id' => $s2->id])
            ->assertJsonMissing(['id' => $s3->id])
            ->assertJsonMissing(['id' => $s4->id])
            ->assertJsonCount(2);
    }

    /** @test */
    function a_caregiver_can_clock_in_to_a_schedule_through_the_web()
    {
        $s1 = $this->createSchedule(Carbon::now(), '12:00:00', 1);

        $this->assertCount(0, Shift::all());

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s1->id])
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $shift = Shift::first();
        $this->assertEquals($s1->id, $shift->schedule_id);
    }

    /** @test */
    function a_caregiver_cannot_submit_a_clock_in_twice_without_clocking_out()
    {
        $this->post("/clock-in", ['client_id'=> $this->client->id])
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $this->assertCount(1, Shift::all());

        $this->post("/clock-in", ['client_id'=> $this->client->id])
            ->assertSee('You are already clocked in');

        $this->assertCount(1, Shift::all());
    }

    /** @test */
    function a_caregiver_should_not_see_schedules_that_have_already_been_clocked_in()
    {
        $s1 = $this->createSchedule(Carbon::now(), '12:00:00', 1);

        $this->get("/caregiver/schedules/{$this->client->id}")
            ->assertJsonCount(1);

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s1->id])
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $this->get("/caregiver/schedules/{$this->client->id}")
            ->assertJsonCount(0);
    }

    /** @test */
    function a_caregiver_cannot_clock_into_the_same_schedule_twice()
    {
        $s1 = $this->createSchedule(Carbon::now(), '12:00:00', 1);

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s1->id])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $clockOut = new ClockOut($this->caregiver);
        $clockOut->clockOut(Shift::first());

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s1->id])
            ->assertStatus(400)
            ->assertSee('This scheduled visit is no longer available to be clocked in to');

        $this->assertEquals(1, Shift::where('schedule_id', $s1->id)->count());
        $this->assertCount(1, Shift::all());
    }

    /** @test */
    function a_caregiver_can_clock_in_again_after_they_have_clocked_out()
    {
        $s1 = $this->createSchedule(Carbon::now(), '12:00:00', 1);
        $s2 = $this->createSchedule(Carbon::now(), '13:00:00', 1);

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s1->id])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $clockOut = new ClockOut($this->caregiver);
        $clockOut->clockOut(Shift::first());

        $this->post("/clock-in", ['client_id'=> $this->client->id, 'schedule_id' => $s2->id])
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'You have successfully clocked in.']);

        $this->assertEquals(1, Shift::where('schedule_id', $s1->id)->count());
        $this->assertEquals(1, Shift::where('schedule_id', $s2->id)->count());
    }
}