<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Console\Commands\CronReminders;
use Illuminate\Support\Facades\Notification;
use App\Schedule;
use Illuminate\Support\Carbon;
use App\Notifications\Caregiver\ShiftReminder;

class TriggerNotificationsTest extends TestCase
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
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->caregivers()->save($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->officeUser->businesses()->attach($this->business->id);
    }

    /** @test */
    public function a_caregiver_should_be_notified_of_upcoming_shifts()
    {
        Notification::fake();

        $schedule = factory(Schedule::class)->create([
            'client_id' => $this->client->id,
            'business_id' => $this->business->id,
            'caregiver_id' => $this->caregiver->id,
            'starts_at' => Carbon::now()->addMinutes(5),
        ]);
        
        Notification::assertNothingSent();

        $cron = new CronReminders();
        $cron->upcomingShifts($this->business);

        Notification::assertSentTo(
            $this->caregiver->user,
            ShiftReminder::class,
            function ($notification, $channels) use ($schedule) {
                return $schedule->id === $notification->schedule->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_not_be_notified_of_shifts_not_in_the_upcoming_window()
    {
        Notification::fake();

        $schedule = factory(Schedule::class)->create([
            'client_id' => $this->client->id,
            'business_id' => $this->business->id,
            'caregiver_id' => $this->caregiver->id,
            'starts_at' => Carbon::now()->subMinutes(1),
        ]);
        
        $cron = new CronReminders();
        $cron->upcomingShifts($this->business);

        Notification::assertNothingSent();

        $schedule = factory(Schedule::class)->create([
            'client_id' => $this->client->id,
            'business_id' => $this->business->id,
            'caregiver_id' => $this->caregiver->id,
            'starts_at' => Carbon::now()->addDays(1),
        ]);
        
        $cron = new CronReminders();
        $cron->upcomingShifts($this->business);

        Notification::assertNothingSent();
    }
}
