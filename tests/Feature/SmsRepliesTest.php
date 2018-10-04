<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Caregiver;
use App\SmsThread;
use App\SmsThreadReply;
use App\PhoneNumber;
use Carbon\Carbon;

class SmsRepliesTest extends TestCase
{
    use RefreshDatabase;

    public $client;
    public $caregiver;
    public $business;
    public $officeUser;
    
    public function setUp()
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->business->update(['outgoing_sms_number' => '8001112222']);

        $this->caregiver = factory('App\Caregiver')->create();
        $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $this->business->caregivers()->save($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
    }

    public function generateThread($overrides = [])
    {
        return SmsThread::create(array_merge([
            'business_id' => $this->business->id,
            'from_number' => PhoneNumber::formatNational($this->business->outgoing_sms_number),
            'message' => str_random(10),
            'can_reply' => true,
            'sent_at' => Carbon::now(),
        ], $overrides));
    }

    public function generateWebhook($to, $from, $message)
    {
        return [
            'MessageSid' => str_random(34),
            'AccountSid' => config('services.twilio.sid'),
            'MessagingServiceSid' => str_random(34),
            'From' => PhoneNumber::formatE164($from),
            'To' => PhoneNumber::formatE164($to),
            'Body' => $message,
            'NumMedia' => 0,
        ];
    }

    public function fakeWebook($to = null, $from = null, $message = null)
    {
        if (empty($to)) {
            $to = config('services.twilio.default_number');
        }

        if (empty($from)) {
            $from = $this->caregiver->phoneNumbers()->first()->number(false);
        }

        if (empty($message)) {
            $message = str_random(100);
        }

        $data = $this->generateWebhook($to, $from, $message);
        $this->post(route('twilio.incoming'), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function twilio_webhook_must_contain_matching_account_sid()
    {
        $data = $this->generateWebhook(config('services.twilio.default_number'), '12017043960', 'test');
        $data['AccountSid'] = 'INVALID_SID';

        $this->post(route('twilio.incoming'), $data)
            ->assertStatus(401);
    }

    /** @test */
    public function the_system_can_receive_txt_messages()
    {
        $this->withoutExceptionHandling();

        $data = $this->generateWebhook(config('services.twilio.default_number'), '+12017043960', 'test');

        $this->post(route('twilio.incoming'), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function a_business_can_create_an_sms_message_with_replies()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $data = [
            'can_reply' => true,
            'message' => 'testing',
            'recipients' => Caregiver::all()->pluck('id')->toArray(),
        ];

        $this->postJson(route('business.communication.text-caregivers'), $data)
            ->assertStatus(200);

        $this->assertCount(1, SmsThread::all());

        $this->assertCount(1, SmsThread::first()->recipients);
    }

    /** @test */
    public function incoming_sms_to_the_business_number_should_save_as_replies()
    {
        $thread = $this->generateThread();
        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);

        $this->assertCount(1, SmsThreadReply::all());
    }

    /** @test */
    public function sms_reply_should_attach_the_user_via_phone_number()
    {
        $caregiver2 = factory('App\Caregiver')->create();
        $caregiver2->user->addPhoneNumber('mobile', '999 555-5555');
        $this->business->caregivers()->save($caregiver2);

        $thread = $this->generateThread();

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);
        $thread->recipients()->create([
            'user_id' => $caregiver2->id,
            'number' => $caregiver2->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number, $this->caregiver->phoneNumbers()->first()->number(false));

        $reply = SmsThreadReply::first();
        $this->assertEquals($this->caregiver->id, $reply->user_id);
        $this->assertNotEquals($caregiver2->id, $reply->user_id);
    }

    /** @test */
    public function if_the_receiving_number_doesnt_match_a_thread_then_thread_can_be_null()
    {
        $this->withoutExceptionHandling();

        $thread = $this->generateThread();

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook('999999999');

        $this->assertNull(SmsThreadReply::first()->sms_thread_id);
        $this->assertNull(SmsThreadReply::first()->business_id);
    }

    /** @test */
    public function if_the_receiving_number_matches_a_thread_and_is_within_2_hours_of_sending_it_will_be_attached_to_the_thread()
    {
        $thread = $this->generateThread(['sent_at' => Carbon::now()->subMinutes(30)]);

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);

        $this->assertEquals($thread->id, SmsThreadReply::first()->sms_thread_id);
        $this->assertEquals($this->business->id, SmsThreadReply::first()->business_id);
    }
    
    /** @test */
    public function if_the_receiving_number_matches_a_thread_and_is_after_2_hours_of_sending_then_thread_will_be_null()
    {
        $this->withoutExceptionHandling();

        $thread = $this->generateThread(['sent_at' => Carbon::now()->subHours(3)]);

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);

        $this->assertNull(SmsThreadReply::first()->sms_thread_id);
        $this->assertEquals($this->business->id, SmsThreadReply::first()->business_id);
    }
    
    /** @test */
    public function when_a_thread_is_not_marked_can_reply_then_only_busiess_is_attached()
    {
        $this->withoutExceptionHandling();

        $thread = $this->generateThread(['can_reply' => false]);

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);

        $this->assertNull(SmsThreadReply::first()->sms_thread_id);
        $this->assertEquals($this->business->id, SmsThreadReply::first()->business_id);
    }

    /** @test */
    public function an_office_user_can_see_a_list_of_only_thier_businesses_sms_threads()
    {
        $this->actingAs($this->officeUser->user);

        for ($i = 0; $i < 5; $i++) {
            $this->generateThread();
        }
        $otherBusiness = factory('App\Business')->create();
        $this->generateThread(['business_id' => $otherBusiness->id]);

        $this->assertCount(6, SmsThread::all());

        $this->getJson(route('business.communication.sms-threads'))
            ->assertStatus(200)
            ->assertJsonCount(5);
    }

    /** @test */
    public function an_office_user_can_get_the_replies_to_a_sms_threads()
    {
        $this->actingAs($this->officeUser->user);

        $thread = $this->generateThread();

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);
    
        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $thread->id,
            ])->assertJsonCount(1, 'replies');
    }

    /** @test */
    public function an_office_user_cannot_see_details_of_another_businesses_sms_thread()
    {
        $this->actingAs($this->officeUser->user);

        $otherBusiness = factory('App\Business')->create();
        $thread = $this->generateThread(['business_id' => $otherBusiness->id]);

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id]))
            ->assertStatus(401);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_replies_not_belonging_to_a_thread()
    {
        $this->actingAs($this->officeUser->user);

        $this->fakeWebook($this->business->outgoing_sms_number);
    
        $thread = $this->generateThread();
        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebook($this->business->outgoing_sms_number);
    
        $this->assertCount(2, SmsThreadReply::all());

        $this->getJson(route('business.communication.sms-other-replies'))
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

}
