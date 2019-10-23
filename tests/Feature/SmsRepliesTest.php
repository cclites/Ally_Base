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
use Tests\FakesTwilioWebhooks;

class SmsRepliesTest extends TestCase
{
    use RefreshDatabase, FakesTwilioWebhooks;

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
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->assignCaregiver($this->caregiver);
        
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

    /** @test */
    public function twilio_webhook_must_contain_matching_account_sid()
    {
        $data = $this->generateWebhook(config('services.twilio.default_number'), '12019999999', 'test');
        $data['AccountSid'] = 'INVALID_SID';

        $this->post(route('telefony.sms.incoming'), $data)
            ->assertStatus(401);
    }

    /** @test */
    public function the_system_can_receive_txt_messages()
    {
        $this->withoutExceptionHandling();

        $data = $this->generateWebhook(config('services.twilio.default_number'), '+12019999999', 'test');

        $this->post(route('telefony.sms.incoming'), $data)
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

        $response = $this->postJson(route('business.communication.text-caregivers'), $data);

        $response->assertStatus(200);
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

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);

        $this->assertCount(1, SmsThreadReply::all());
    }

    /** @test */
    public function sms_reply_should_attach_the_user_via_phone_number()
    {
        $caregiver2 = factory('App\Caregiver')->create();
        $caregiver2->user->addPhoneNumber('mobile', '999 555-5555');
        $this->business->assignCaregiver($caregiver2);

        $thread = $this->generateThread();

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);
        $thread->recipients()->create([
            'user_id' => $caregiver2->id,
            'number' => $caregiver2->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);

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

        $this->fakeWebhook('999999999', $this->caregiver);

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

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);

        $this->assertCount(1, $thread->fresh()->replies);
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

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);

        $this->assertCount(0, $thread->fresh()->replies);
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

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);

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

        $this->getJson(route('business.communication.sms-threads')."?json=1")
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

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);
    
        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id])."?json=1")
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

        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id])."?json=1")
            ->assertStatus(403);
    }

    /** @test */
    public function an_office_user_can_get_a_list_of_replies_not_belonging_to_a_thread()
    {
        $this->actingAs($this->officeUser->user);

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);
    
        $thread = $this->generateThread();
        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);
    
        $this->assertCount(2, SmsThreadReply::all());

        $this->getJson(route('business.communication.sms-other-replies'))
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function caregivers_should_only_be_attached_to_threads_they_are_a_part_of()
    {
        $otherCaregiver = factory('App\Caregiver')->create();
        $number = $otherCaregiver->user->addPhoneNumber('primary', '1 (999) 999-8888');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->assignCaregiver($otherCaregiver);
        
        $thread = $this->generateThread(['sent_at' => Carbon::now()->subMinutes(30)]);

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebhook($this->business->outgoing_sms_number, $otherCaregiver);

        $this->assertCount(0, $thread->fresh()->replies);
        $this->assertNull(SmsThreadReply::first()->sms_thread_id);
    }

    /** @test */
    public function office_users_can_search_sms_threads_by_date()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $thread1 = $this->generateThread(['sent_at' => Carbon::now()->subDays(10)]);
        $thread2 = $this->generateThread(['sent_at' => Carbon::now()->subDays(5)]);
        $thread3 = $this->generateThread(['sent_at' => Carbon::now()->subDays(5)]);
        $thread4 = $this->generateThread(['sent_at' => Carbon::now()->subDays(1)]);
        $thread5 = $this->generateThread(['sent_at' => Carbon::now()->subDays(1)]);

        $this->assertCount(5, SmsThread::all());

        $start = Carbon::now()->subDays(7)->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        $query = "?json=1&start_date=$start&end_date=$end";

        $this->getJson(route('business.communication.sms-threads').$query)
            ->assertStatus(200)
            ->assertJsonCount(4);

        $start = Carbon::now()->subDays(2)->format('Y-m-d');
        $end = Carbon::now()->format('Y-m-d');
        $query = "?json=1&start_date=$start&end_date=$end";

        $this->getJson(route('business.communication.sms-threads').$query)
            ->assertStatus(200)
            ->assertJsonCount(2);

        $start = Carbon::now()->subDays(11)->format('Y-m-d');
        $end = Carbon::now()->subDays(8)->format('Y-m-d');
        $query = "?json=1&start_date=$start&end_date=$end";

        $this->getJson(route('business.communication.sms-threads').$query)
            ->assertStatus(200)
            ->assertJsonCount(1);

        $start = Carbon::now()->subDays(30)->format('Y-m-d');
        $end = Carbon::now()->subDays(15)->format('Y-m-d');
        $query = "?json=1&start_date=$start&end_date=$end";

        $this->getJson(route('business.communication.sms-threads').$query)
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    /** @test */
    public function office_users_can_filter_sms_threads_by_those_with_replies()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $thread1 = $this->generateThread();
        $thread2 = $this->generateThread();
        $thread3 = $this->generateThread();

        $this->assertCount(3, SmsThread::all());

        factory(SmsThreadReply::class)->create(['sms_thread_id' => $thread1->id]);

        $this->getJson(route('business.communication.sms-threads').'?json=1&reply_only=0')
            ->assertStatus(200)
            ->assertJsonCount(3);

        $this->getJson(route('business.communication.sms-threads').'?json=1&reply_only=1')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function when_an_office_user_views_a_thread_it_should_mark_all_replies_as_read()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->officeUser->user);

        $thread = $this->generateThread();

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        factory(SmsThreadReply::class, 3)->create(['sms_thread_id' => $thread->id]);
        $this->assertEquals(3, $thread->fresh()->unread_replies_count);

        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id])."?json=1")
            ->assertStatus(200)
            ->assertJsonCount(3, 'replies');

        $this->assertEquals(0, $thread->fresh()->unread_replies_count);

        factory(SmsThreadReply::class)->create(['sms_thread_id' => $thread->id]);
        $this->assertEquals(1, $thread->fresh()->unread_replies_count);

        $this->getJson(route('business.communication.sms-threads.show', ['thread' => $thread->id])."?json=1")
            ->assertStatus(200)
            ->assertJsonCount(4, 'replies');

        $this->assertEquals(0, $thread->fresh()->unread_replies_count);
    }

    /** @test */
    public function no_reply_is_saved_if_a_message_and_media_url_are_not_supplied(){

        $this->withoutExceptionHandling();

        $data = $this->generateWebhook(config('services.twilio.default_number'), '+12019999999', '');

        $this->post(route('telefony.sms.incoming'), $data)
            ->assertStatus(200)
            ->assertSeeText('The body field is required');

        $this->assertEquals(0, SmsThreadReply::count());
    }

    /** @test */
    public function reply_is_saved_if_message_is_blank_and_media_url_is_not_blank(){

        $this->withoutExceptionHandling();

        $data = $this->generateWebhook(config('services.twilio.default_number'), '+12019999999', '');
        $data["MediaUrl"] = str_random(10);
        $this->assertEmpty($data['Body']);

        $this->post(route('telefony.sms.incoming'), $data)
            ->assertStatus(200);

        $reply = SmsThreadReply::first();
        $this->assertEquals($data['MediaUrl'], $reply->media_url);
        $this->assertNull($reply->message);
    }

    /** @test */
    public function a_reply_can_have_a_media_url(){

        $this->withoutExceptionHandling();

        $data = $this->generateWebhook(config('services.twilio.default_number'), '+12019999999', 'test');
        $data["MediaUrl"] = str_random(10);

        $this->post(route('telefony.sms.incoming'), $data)
            ->assertStatus(200);

        $reply = SmsThreadReply::first();
        $this->assertEquals($data['MediaUrl'], $reply->media_url);
        $this->assertEquals('test', $reply->message);
    }
}
