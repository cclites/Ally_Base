<?php

namespace Tests\Feature;

use App\CommunicationLog;
use App\Notifications\CaregiverWelcomeEmail;
use App\Services\PhoneService;
use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommunicationLogTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();

        $this->createBusinessWithUsers();
    }

    /** @test */
    function it_logs_every_outgoing_email()
    {
        $this->assertEquals('ally', config('mail.driver'));
        $this->assertCount(0, CommunicationLog::all());

        $this->caregiver->notify(new CaregiverWelcomeEmail($this->caregiver, $this->chain));

        $this->assertCount(1, CommunicationLog::all());
        $log = CommunicationLog::first();
        $this->assertEquals($this->caregiver->email, $log->to);
        $this->assertEquals('mail', $log->channel);
        $this->assertStringContainsString('Click here to confirm', $log->body);
    }

    /** @test */
    function it_logs_every_outgoing_sms()
    {
        $this->assertEquals('log', config('sms.driver'));
        $this->assertCount(0, CommunicationLog::all());

        $service = new PhoneService;
        $service->setFromNumber('1234567890');
        $service->sendTextMessage('5555551000', 'test an sms');

        $this->assertCount(1, CommunicationLog::all());
        $log = CommunicationLog::first();
        $this->assertEquals('test an sms', $log->body);
        $this->assertEquals('1234567890', $log->from);
        $this->assertEquals('5555551000', $log->to);
        $this->assertEquals('sms', $log->channel);
    }
}
