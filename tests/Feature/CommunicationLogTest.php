<?php

namespace Tests\Feature;

use App\CommunicationLog;
use App\Notifications\CaregiverWelcomeEmail;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommunicationLogTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();
        $this->business->update(['outgoing_sms_number' => '8001112222']);
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
    }

    /** @test */
    function it_logs_every_saved_notification_email()
    {
//        $this->assertEquals('ally', config('mail.driver'));
        $this->assertCount(0, CommunicationLog::all());

        $this->caregiver->notify(new CaregiverWelcomeEmail($this->caregiver, $this->chain));

        $this->assertCount(1, CommunicationLog::all());
        $log = CommunicationLog::first();
        $this->assertEquals($this->caregiver->email, $log->email);
        $this->assertEquals('mail', $log->channel);
        $this->assertEquals($this->caregiver->id, $log->user_id);
    }
}
