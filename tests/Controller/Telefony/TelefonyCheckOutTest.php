<?php
namespace Tests\Controller\Telefony;

use App\Activity;
use App\Http\Controllers\Api\Telefony\TelefonyCheckOutController;
use App\PhoneNumber;

class TelefonyCheckOutTest extends TelefonyBase
{
    public function test_check_out_response_with_an_active_client_shift()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/response');
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $this->caregiver->firstname . ' clocking out, press 2');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-out', [$shift]) . '">');
    }

    public function test_check_out_response_without_an_active_shift()
    {
        $response = $this->telefonyPost('check-out/response');
        $response->assertSee('<Say');
        $response->assertSee('Please enter the last 4 digits of your phone number for identification');
        $response->assertSee('<Gather numDigits="4" action="' . route('telefony.check-out.accept-digits') . '">');
    }

    public function test_accept_caregiver_phone_number_digits_with_shift()
    {
        $shift = $this->createShift();
        $phone = factory(PhoneNumber::class)->make(['national_number' => '5555551000']);
        $this->caregiver->phoneNumbers()->save($phone);
        $response = $this->telefonyPost('check-out/accept-digits', ['Digits' => 1000]);
        $response->assertSee('<Say');
        $response->assertSee('If this is ' . $this->caregiver->firstname . ', press 2 to continue clocking out');
        $response->assertSee('<Gather numDigits="1" action="' . route('telefony.check-out', [$shift]) . '">');
    }

    public function test_accept_caregiver_phone_number_digits_without_shift()
    {
        $phone = factory(PhoneNumber::class)->make(['national_number' => '5555551000']);
        $this->caregiver->phoneNumbers()->save($phone);
        $response = $this->telefonyPost('check-out/accept-digits', ['Digits' => 1000]);
        $response->assertSee('<Say');
        $response->assertSee('You entered, 1,,0,,0,,0, but ' . $this->caregiver->firstname . ' is not clocked in');
    }

    public function test_check_out_on_a_shift_asks_about_mileage()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/' . $shift->id, ['Digits' => 2]);
        $response->assertSee('<Say');
        $response->assertSee(TelefonyCheckOutController::PromptForMileage);
        $response->assertSee('<Gather timeout="5" numDigits="1" action="' . route('telefony.check-out.check-for-mileage', [$shift]) . '">');
    }

//    public function test_injury_can_be_recorded()
//    {
//        $shift = $this->createShift();
//        $response = $this->telefonyPost('check-out/check-for-injury/' . $shift->id, ['Digits' => 2]);
//        $this->assertEquals(1, $shift->issues()->first()->caregiver_injury);
//        $response->assertSee('<Redirect');
//        $response->assertSee(route('telefony.check-out.check-for-activities', [$shift]));
//    }

//    public function test_when_no_injuries_are_recorded_then_check_for_mileage()
//    {
//        $shift = $this->createShift();
//        $response = $this->telefonyPost('check-out/check-for-injury/' . $shift->id, ['Digits' => 1]);
//        $response->assertSee('<Say');
//        $response->assertSee(TelefonyCheckOutController::PromptForMileage);
//        $response->assertSee('<Gather timeout="5" numDigits="1" action="' . route('telefony.check-out.check-for-mileage', [$shift]) . '">');
//    }

    /** @test */
    function check_for_mileage_response()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/check-for-mileage/' . $shift->id);

        $response->assertSee('<Say');
        $response->assertSee(TelefonyCheckOutController::PromptForMileage);
        $response->assertSee('<Gather timeout="5" numDigits="1" action="' . route('telefony.check-out.check-for-mileage', [$shift]) . '">');
    }

    /** @test */
    function if_mileage_is_skipped_then_prompt_for_activities()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/check-for-mileage/' . $shift->id, ['Digits' => 1]);
        $this->checkActivitiesResponse($response, $shift);
    }

    /** @test */
    function check_ask_for_mileage_entry()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/check-for-mileage/' . $shift->id, ['Digits' => 2]);

        $response->assertSee('<Say');
        $response->assertSee(TelefonyCheckOutController::AskForMileageEntry);
        $response->assertSee('<Gather timeout="30" finishOnKey="#" action="' . route('telefony.check-out.confirm-mileage', [$shift]) . '">');
    }

    /** @test */
    function check_mileage_is_confirmed()
    {
        $shift = $this->createShift();
        $mileage = 13;
        $response = $this->telefonyPost('check-out/confirm-mileage/' . $shift->id, ['Digits' => $mileage]);

        $response->assertSee(sprintf(TelefonyCheckOutController::ConfirmMileageEntry, $mileage));
        $response->assertSee('<Gather timeout="10" numDigits="1" action="' . route('telefony.check-out.record-mileage', [$shift, $mileage]) . '">');
    }

    /** @test */
    function check_mileage_can_be_recorded()
    {
        $shift = $this->createShift();
        $mileage = 13;
        $response = $this->telefonyPost('check-out/record-mileage/'.$shift->id.'/'.$mileage, ['Digits' => 1]);
        $response->assertSee(TelefonyCheckOutController::MileageEntrySuccess);
        $response->assertSee('<Redirect');
        $response->assertSee(route('telefony.check-out.check-for-activities', [$shift]));

        $this->assertEquals($mileage, $shift->fresh()->mileage);
    }

    /** @test */
    function check_mileage_can_be_re_entered()
    {
        $shift = $this->createShift();
        $mileage = 13;
        $response = $this->telefonyPost('check-out/record-mileage/'.$shift->id.'/'.$mileage, ['Digits' => 2]);
        $response->assertSee('<Say');
        $response->assertSee(TelefonyCheckOutController::AskForMileageEntry);
        $response->assertSee('<Gather timeout="30" finishOnKey="#" action="' . route('telefony.check-out.confirm-mileage', [$shift]) . '">');
    }

    /** @test */
    function if_an_empty_value_is_submitted_for_mileage_it_should_ask_again()
    {
        $shift = $this->createShift();
        $mileage = "string";
        $response = $this->telefonyPost('check-out/confirm-mileage/' . $shift->id, ['Digits' => $mileage]);

        $response->assertSee('<Redirect');
        $response->assertSee(route('telefony.check-out.check-for-mileage', [$shift]));
    }

    public function test_check_for_activities_response()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/check-for-activities/' . $shift->id);
        $this->checkActivitiesResponse($response, $shift);
    }

    protected function checkActivitiesResponse($response, $shift)
    {
        $response->assertSee('<Say');
        $response->assertSee('Please enter the numerical code of any activity performed on your shift followed by a pound sign.');
        $response->assertSee('<Gather timeout="30" finishOnKey="#" action="' . route('telefony.check-out.confirm-activity', [$shift]) . '">');
    }

    public function test_say_all_activities()
    {
        $shift = $this->createShift();
        $activity = factory(Activity::class)->create(['code' => '001']);
        $response = $this->telefonyPost('check-out/confirm-activity/' . $shift->id, ['Digits' => '0']);
        $response->assertSee('<Gather timeout="10" finishOnKey="#" action="' . route('telefony.check-out.confirm-activity', [$shift]) . '">');
        $response->assertSee('The activity codes are as follows.  You may enter them at any time followed by the pound sign.');
        $response->assertSee('.. 0,,0,,1, ' . $activity->name);

    }

    public function test_entered_activity_code_is_confirmed()
    {
        $shift = $this->createShift();
        $activity = factory(Activity::class)->create(['code' => '001']);
        $response = $this->telefonyPost('check-out/confirm-activity/' . $shift->id, ['Digits' => '001']);
        $response->assertSee('You have entered, ' . $activity->name . '.  If this is correct, Press 1');
        $response->assertSee('<Gather timeout="10" numDigits="1" action="' . route('telefony.check-out.record-activity', [$shift, $activity]) . '">');
    }

    public function test_activity_can_be_recorded()
    {
        $shift = $this->createShift();
        $activity = factory(Activity::class)->create();
        $response = $this->telefonyPost('check-out/record-activity/' . $shift->id . '/' . $activity->id, ['Digits' => 1]);
        $this->assertEquals($activity->name, $shift->activities()->first()->name);
        $response->assertSee('The activity has been recorded.');
        $response->assertSee('<Redirect');
        $response->assertSee(route('telefony.check-out.check-for-activities', [$shift]));
    }

    public function test_no_digits_on_activities_redirects_to_finalize()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/check-for-activities/' . $shift->id);
        $response->assertSee('<Redirect');
        $response->assertSee(route('telefony.check-out.finalize', [$shift]));
    }

    public function test_finalize_check_out()
    {
        $shift = $this->createShift();
        $response = $this->telefonyPost('check-out/finalize/' . $shift->id);
        $response->assertSee('You have successfully clocked out.');
        $this->assertFalse($shift->fresh()->statusManager()->isClockedIn());
    }
}