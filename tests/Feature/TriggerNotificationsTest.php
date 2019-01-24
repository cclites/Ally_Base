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
use App\Notifications\Caregiver\ClockInReminder;
use App\Notifications\Caregiver\ClockOutReminder;
use App\Shifts\ClockIn;
use App\Shift;
use App\Console\Commands\CronVisitAccuracyReminder;
use App\Notifications\Caregiver\VisitAccuracyCheck;
use App\CaregiverLicense;
use App\Console\Commands\CronDailyNotifications;

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

    /**
     * Helper function to create a schedule entry.
     *
     * @param Carbon $startsAt
     * @param integer $duration
     * @return Schedule
     */
    public function createSchedule(Carbon $startsAt = null, int $duration = 60) : Schedule
    {
        if ($startsAt == null) {
            $startsAt = Carbon::now();
        }

        return factory(Schedule::class)->create([
            'client_id' => $this->client->id,
            'business_id' => $this->business->id,
            'caregiver_id' => $this->caregiver->id,
            'starts_at' => $startsAt,
            'duration' => $duration,
        ]);
    }

    /**
     * Helper function to create clocked in shift from a Schedule.
     *
     * @param Schedule $schedule
     * @return Shift
     */
    public function clockInToShift(Schedule $schedule) : Shift
    {
        $clockIn = new ClockIn($this->caregiver);
        $clockIn->setManual(true);
        return $clockIn->clockIn($schedule);
    }

    /** @test */
    public function a_caregiver_should_be_notified_of_upcoming_shifts()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->addMinutes(5));
        
        Notification::assertNothingSent();

        (new CronReminders())->handle();

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

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(1));
        
        (new CronReminders())->handle();

        Notification::assertNothingSent();

        $schedule = $this->createSchedule(Carbon::now()->addDays(1));
        
        (new CronReminders())->handle();

        Notification::assertNothingSent();
    }

    /** @test */
    public function a_caregiver_should_be_notified_if_they_are_late_to_clock_in()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(25));
        
        Notification::assertNothingSent();

        (new CronReminders())->handle();

        Notification::assertSentTo(
            $this->caregiver->user,
            ClockInReminder::class,
            function ($notification, $channels) use ($schedule) {
                return $schedule->id === $notification->schedule->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_be_notified_if_they_forget_to_clock_out()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(80), 60);
        
        $shift = $this->clockInToShift($schedule);
            
        Notification::assertNothingSent();

        (new CronReminders())->handle();

        Notification::assertSentTo(
            $this->caregiver->user,
            ClockOutReminder::class,
            function ($notification, $channels) use ($shift) {
                return $shift->id === $notification->shift->id;
            }
        );
    }

    /** @test */
    public function caregivers_with_recently_unconfirmed_visits_should_receive_the_visit_accuracy_notification()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(80), 60);
        $shift = $this->clockInToShift($schedule);
        
        Notification::assertNothingSent();

        (new CronVisitAccuracyReminder())->handle();

        Notification::assertSentTo(
            $this->caregiver->user,
            VisitAccuracyCheck::class
        );
    }

    /** @test */
    public function caregivers_without_recent_unconfirmed_visits_should_not_receive_the_visit_accuracy_notification()
    {
        Notification::fake();

        (new CronVisitAccuracyReminder())->handle();

        Notification::assertNothingSent();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(80), 60);
        $shift = $this->clockInToShift($schedule);
        $shift->update(['status' => Shift::WAITING_FOR_PAYOUT]);

        (new CronVisitAccuracyReminder())->handle();

        Notification::assertNothingSent();

        $shift->update(['checked_in_time' => Carbon::now()->subDays(60)]);

        (new CronVisitAccuracyReminder())->handle();

        Notification::assertNothingSent();
    }

    /** @test */
    public function a_caregiver_should_be_notified_if_they_have_a_license_expiring_soon()
    {
        Notification::fake();

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->addDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);
        
        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        Notification::assertSentTo(
            $this->caregiver->user,
            \App\Notifications\Caregiver\CertificationExpiring::class,
            function ($notification, $channels) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }

    /** @test */
    public function office_users_should_be_notified_when_a_caregivers_license_is_expiring()
    {
        Notification::fake();

        // create a second office user to another business on the same chain
        $otherBusiness = factory('App\Business')->create();
        $otherBusiness->chain->caregivers()->save($this->caregiver);
        $otherOfficeUser = factory('App\OfficeUser')->create();
        $otherOfficeUser->businesses()->attach($otherBusiness->id);

        // create a third office user to a third business on another chain
        $otherChain = factory('App\BusinessChain')->create();
        $thirdBusiness = factory('App\Business')->create(['chain_id' => $otherChain->id]);
        $otherChain->caregivers()->save($this->caregiver);
        $thirdOfficeUser = factory('App\OfficeUser')->create();
        $thirdOfficeUser->businesses()->attach($thirdBusiness->id);

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->addDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);
        
        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            function ($notification, $channels) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $otherOfficeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            function ($notification, $channels) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $thirdOfficeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            function ($notification, $channels) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }
}
