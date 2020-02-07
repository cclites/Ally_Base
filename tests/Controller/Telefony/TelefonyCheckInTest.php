<?php
namespace Tests\Controller\Telefony;

use App\Caregiver;
use App\Http\Controllers\Api\Telefony\TelefonyCheckInController;
use App\PhoneNumber;
use App\Shift;
use Carbon\Carbon;

class TelefonyCheckInTest extends TelefonyBase
{
    public function test_check_in_response_with_a_schedule()
    {
        $schedule = $this->createSchedule();
        $response = $this->telefonyPost('check-in/response');
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $this->caregiver->firstname . ' clocking in, press 1');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-in', [$schedule->caregiver]) . '">');
    }

    public function test_check_in_response_without_a_schedule()
    {
        $response = $this->telefonyPost('check-in/response');
        $response->assertSee('<Say');
        $response->assertSee(TelefonyCheckInController::PromptForCaregiverPhone);
        $response->assertSee('<Gather numDigits="10" action="' . route('telefony.check-in.accept-digits') . '">');
    }

    public function test_accept_caregiver_phone_number_digits()
    {
        $phone = factory(PhoneNumber::class)->make(['national_number' => '1234567890']);
        $this->caregiver->phoneNumbers()->save($phone);
        $response = $this->telefonyPost('check-in/accept-digits', ['Digits' => 1234567890]);
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $this->caregiver->firstname . ', press 1 to finish clocking in');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-in', [$this->caregiver]) . '">');
    }

    public function test_accept_unassigned_caregiver_phone_number_digits()
    {
        // Tests a caregiver of the same business but not assigned to the client
        $caregiver = factory(Caregiver::class)->create();
        $this->business->assignCaregiver($caregiver);
        $phone = factory(PhoneNumber::class)->make(['national_number' => '1234567890']);
        $caregiver->phoneNumbers()->save($phone);
        $response = $this->telefonyPost('check-in/accept-digits', ['Digits' => 1234567890]);
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $caregiver->firstname . ', press 1 to finish clocking in');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-in', [$caregiver]) . '">');
    }

    public function test_check_in_of_caregiver_with_schedule()
    {
        $schedule = $this->createSchedule();
        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee('You have successfully clocked in.');
        $this->assertTrue(Shift::where('schedule_id', $schedule->id)->exists());
    }

    public function test_check_in_of_caregiver_without_schedule()
    {
        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee('You have successfully clocked in.');
        $this->assertTrue(Shift::where('caregiver_id', $this->caregiver->id)->exists());
    }

    /** @test */
    function it_will_hang_up_if_the_caregiver_is_already_clocked_in()
    {
        $schedule = $this->createSchedule();
        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee('You have successfully clocked in.');

        $shift = Shift::where(['schedule_id' => $schedule->id])
            ->where(['caregiver_id' => $this->caregiver->id]);

        $this->assertTrue($shift->exists());
        $this->assertTrue($this->caregiver->isClockedIn());

        $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1])
            ->assertSee(TelefonyCheckInController::AlreadyClockedOutMessage);
    }

    /** @test */
    function a_caregiver_should_not_be_able_to_clock_into_the_same_schedule_twice()
    {
        $schedule = $this->createSchedule();
        $this->assertCount(0, $schedule->shifts);

        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee('You have successfully clocked in.');
        $this->assertCount(1, $schedule->fresh()->shifts);

        /** @var \App\Shift $shift */
        $shift = $schedule->shifts()->first();
        $shift->update(['checked_out_time' => Carbon::now()]);
        $shift->statusManager()->ackClockOut(false);

        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee('You have successfully clocked in.');
        $this->assertCount(1, $schedule->fresh()->shifts);
        $this->assertCount(2, $this->caregiver->fresh()->shifts);
    }

    /** @test */
    function test_telefony_greeting_allow_post_web_hooks()
    {
        // This resolves a problem where outgoing business sms numbers
        // are set up to post webhooks.  Adding this url is faster
        // than updating 50 numbers config
        $this->post("/api/telefony", [], ['Content-Type' => 'text/xml'])
            ->assertStatus(200);
    }
}
