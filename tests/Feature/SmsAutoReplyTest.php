<?php

namespace Tests\Feature;

use App\Events\SmsThreadReplyCreated;
use App\Jobs\SendTextMessage;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\SmsThreadReply;
use Carbon\Carbon;
use Tests\FakesTwilioWebhooks;
use App\Listeners\HandleSmsAutoReply;

class SmsAutoReplyTest extends TestCase{
    use RefreshDatabase, FakesTwilioWebhooks;

    public $client;
    public $caregiver;
    public $business;
    public $officeUser;

    public function setUp() : void
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->business->update(['outgoing_sms_number' => '8001112222']);

        $this->caregiver = factory('App\Caregiver')->create();
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->assignCaregiver($this->caregiver);

        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /**
     * Helper to trigger an incoming SMS.
     *
     * @param $business
     * @param $caregiver
     * @param $message
     */
    public function fakeIncomingSms($business, $caregiver, $message = 'Test')
    {
        $reply = SmsThreadReply::create([
            'business_id' => $business->id,
            'sms_thread_id' => null,
            'user_id' => $caregiver->id,
            'from_number' => $caregiver->smsNumber->national_number,
            'to_number' => $business->outgoing_sms_number,
            'message' => $message,
            'twilio_message_id' => md5(Carbon::now()->toDateTimeString()),
        ]);

        $event = new SmsThreadReplyCreated($reply);
        $handler = new HandleSmsAutoReply();
        $handler->handle($event);
    }

    /** @test */
    function a_handle_sms_auto_reply_listener_should_trigger_when_a_new_reply_is_created()
    {
        Queue::fake();

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver, 'test');

        Queue::assertPushed(CallQueuedListener::class, function ($job) {
            return $job->class == HandleSmsAutoReply::class;
        });
    }

    /** @test */
    function auto_reply_when_a_reply_comes_in_between_valid_weekday_times()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'schedule',
            'week_start'=>'17:00', // 5PM
            'week_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/19/2019 01:00:00', $this->business->timezone));  // wednesday at 1:00 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        Carbon::setTestNow(Carbon::parse('06/19/2019 18:00:00', $this->business->timezone));  // wednesday at 6:00 PM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        \Queue::assertPushed(SendTextMessage::class, 2, function ($event) use ($config) {
            return $event->to == $this->caregiver->smsNumber->national_number
                && $config->message == $event->message;
        });
    }

    /** @test */
    function dont_auto_reply_when_a_reply_comes_in_outside_valid_weekday_times()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'schedule',
            'week_start'=>'17:00', // 5PM
            'week_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/19/2019 10:00:00', $this->business->timezone));  // wednesday at 10:00 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);
        \Queue::assertNotPushed(SendTextMessage::class);
    }

    /** @test */
    function auto_reply_when_a_reply_comes_in_between_valid_weekend_times()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'schedule',
            'weekend_start'=>'17:00', // 5PM
            'weekend_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/22/2019 01:00:00', $this->business->timezone));  // saturday at 1:00 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        \Queue::assertPushed(SendTextMessage::class, function ($event) use ($config) {
            return $event->to == $this->caregiver->smsNumber->national_number
                && $config->message == $event->message;
        });
    }

    /** @test */
    function dont_auto_reply_when_a_reply_comes_in_outside_valid_weekend_times()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'schedule',
            'weekend_start'=>'17:00', // 5PM
            'weekend_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/22/2019 10:00:00', $this->business->timezone));  // saturday at 10:00 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);
        \Queue::assertNotPushed(SendTextMessage::class);
    }

    /** @test */
    function auto_reply_all_weekend_when_weekend_start_and_end_are_the_same()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'schedule',
            'weekend_start'=>'00:00', // 12:01 AM
            'weekend_end'=>'00:00' // 12:01 AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/22/2019 00:01:00', $this->business->timezone));  // saturday at 12:01 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        Carbon::setTestNow(Carbon::parse('06/22/2019 11:59:00', $this->business->timezone));  // saturday at 11:59 PM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        Carbon::setTestNow(Carbon::parse('06/23/2019 00:01:00', $this->business->timezone));  // sunday at 12:01 AM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        Carbon::setTestNow(Carbon::parse('06/23/2019 11:59:00', $this->business->timezone));  // sunday at 11:59 PM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        \Queue::assertPushed(SendTextMessage::class, 4, function ($event) use ($config) {
            return $event->to == $this->caregiver->smsNumber->national_number
                && $config->message == $event->message;
        });
    }

    /** @test */
    function auto_reply_when_reply_option_is_on()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'on',
            'week_start'=>'17:00', // 5PM
            'week_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/19/2019 12:00:00', $this->business->timezone));  // wednesday at 12:00 PM
        $this->fakeIncomingSms($this->business, $this->caregiver);

        \Queue::assertPushed(SendTextMessage::class, function ($event) use ($config) {
            return $event->to == $this->caregiver->smsNumber->national_number
                && $config->message == $event->message;
        });
    }

    /** @test */
    function dont_auto_reply_when_reply_option_is_off()
    {
        Queue::fake();

        $config = factory('App\BusinessCommunications')->create([
            'reply_option' => 'off',
            'week_start'=>'17:00', // 5PM
            'week_end'=>'08:00' // 8AM
        ]);

        Carbon::setTestNow(Carbon::parse('06/19/2019 12:00:00', $this->business->timezone));  // wednesday at 12:00 PM
        $this->fakeIncomingSms($this->business, $this->caregiver);
        \Queue::assertNotPushed(SendTextMessage::class);
    }
}
