<?php
namespace Tests\Controller\Telefony;

use App\Caregiver;
use App\Http\Controllers\Api\Telefony\TelefonyCheckInController;
use App\PhoneNumber;
use App\Shift;

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

        $response = $this->telefonyPost('check-in/' . $this->caregiver->id, ['Digits' => 1]);
        $response->assertSee(TelefonyCheckInController::AlreadyClockedOutMessage);
    }
}
