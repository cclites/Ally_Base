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
use App\Listeners\HandleSmsAutoReply;

use Log;



class AutoReplyNotificationTest extends TestCase{
    use RefreshDatabase, FakesTwilioWebhooks;

    public $client;
    public $caregiver;
    public $business;
    public $officeUser;
    public $smsAutoResponseSettings;
    public $outgoingSmsNumber = '(561) 417-9272';


    public function setUp()
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();
        $this->business = $this->client->business;
        $this->business->update(['phone1' => $this->outgoingSmsNumber]);

        $this->caregiver = factory('App\Caregiver')->create();
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->assignCaregiver($this->caregiver);

        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);

        //$this->businessCommunications = factory('App\BusinessCommunications')->create(['business_id'=>$this->business->id]);
        //$this->businessCommunications->update(['business_id'=>$this->business->id]);
        //$this->business->autoResponseSettings = $this->smsAutoResponseSettings;
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

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testHasBusinessCommunicationsSettings(){

        Log::info("Has communication settings");
        $this->businessCommunications = factory('App\BusinessCommunications')->create(['business_id'=>$this->business->id]);
        $data = $this->generateWebhook($this->outgoingSmsNumber, '+12019999999', 'test');
        $this->post(route('telefony.sms.incoming'), $data)->assertStatus(200);

    }

    public function testDoNotSend(){

        Log::info("Has no active settings");
        $this->businessCommunications = factory('App\BusinessCommunications')
                            ->create(['business_id'=>$this->business->id,
                                      'on_indefinitely'=>false,
                                      'auto_off'=>true
                                     ]);

        $data = $this->generateWebhook($this->outgoingSmsNumber, '+12019999999', 'test');
        $this->post(route('telefony.sms.incoming'), $data)->assertStatus(200);
    }

    /*
    public function testSendForRange(){
        $this->businessCommunications = factory('App\BusinessCommunications')
            ->create(['business_id'=>$this->business->id,
                'on_indefinitely'=>false,
                'auto_off'=>false,
                'week_end'=>'23:00'
            ]);

        $data = $this->generateWebhook($this->outgoingSmsNumber, '+12019999999', 'test');
        $this->post(route('telefony.sms.incoming'), $data)->assertStatus(200);
    }*/

    public function testShouldSend(){
        Log::info("should send");
        $this->businessCommunications = factory('App\BusinessCommunications')
            ->create(['business_id'=>$this->business->id,
                'on_indefinitely'=>false,
                'auto_off'=>false,
                'week_start'=>'24:00',
                'week_end'=>'23:00'
            ]);

        $data = $this->generateWebhook($this->outgoingSmsNumber, '+12019999999', 'test');
        $this->post(route('telefony.sms.incoming'), $data)->assertStatus(200);

    }

    public function testShouldNotSend(){
        Log::info("Should not send");
        $this->businessCommunications = factory('App\BusinessCommunications')
            ->create(['business_id'=>$this->business->id,
                'on_indefinitely'=>false,
                'auto_off'=>false,
                'week_start'=>'23:00',
                'week_end'=>'5:00'
            ]);

        $data = $this->generateWebhook($this->outgoingSmsNumber, '+12019999999', 'test');
        $this->post(route('telefony.sms.incoming'), $data)->assertStatus(200);

    }


}
