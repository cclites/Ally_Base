<?php
namespace Tests\Controller\Telefony;

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
        $response->assertSee('Please enter the last 4 digits of your phone number for identification');
        $response->assertSee('<Gather numDigits="4" action="' . route('telefony.check-in.accept-digits') . '">');
    }

    public function test_accept_caregiver_phone_number_digits()
    {
        $phone = factory(PhoneNumber::class)->make(['national_number' => '5555551000']);
        $this->caregiver->phoneNumbers()->save($phone);
        $response = $this->telefonyPost('check-in/accept-digits', ['Digits' => 1000]);
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $this->caregiver->firstname . ', press 1 to finish clocking in');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-in', [$this->caregiver]) . '">');
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
}