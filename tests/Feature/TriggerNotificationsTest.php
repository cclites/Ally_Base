<?php

namespace Tests\Feature;

use App\CaregiverApplication;
use App\PhoneNumber;
use App\SmsThread;
use Illuminate\Support\Str;
use Tests\FakesTwilioWebhooks;
use Tests\TestCase;
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
use App\Shifts\ClockOut;
use App\SmsThreadReply;
use App\TriggeredReminder;
use App\Console\Commands\CronFlushTriggeredReminders;
use App\Channels\SystemChannel;
use App\Channels\SmsChannel;

class TriggerNotificationsTest extends TestCase
{
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
        $this->caregiver->clients()->save($this->client);
        $number = $this->caregiver->user->addPhoneNumber('primary', '1 (234) 567-8900');
        $number->update(['receives_sms' => 1]);
        $this->business->chain->assignCaregiver($this->caregiver);

        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
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
     * @return Shift|null
     */
    public function clockInToShift(Schedule $schedule) : ?Shift
    {
        $clockIn = new ClockIn($this->caregiver);
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
            function ($notification) use ($schedule) {
                return $schedule->id === $notification->schedule->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_only_be_notified_once_of_upcoming_shifts()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->addMinutes(5));

        Notification::assertNothingSent();

        (new CronReminders())->handle();

        (new CronReminders())->handle();

        Notification::assertSentToTimes(
            $this->caregiver->user,
            ShiftReminder::class,
            1
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
            function ($notification) use ($schedule) {
                return $schedule->id === $notification->schedule->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_only_be_notified_once_if_they_are_late_to_clock_in()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(25));

        Notification::assertNothingSent();

        (new CronReminders())->handle();

        (new CronReminders())->handle();

        Notification::assertSentToTimes(
            $this->caregiver->user,
            ClockInReminder::class,
            1
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
            function ($notification) use ($shift) {
                return $shift->id === $notification->shift->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_only_be_notified_once_if_they_forget_to_clock_out()
    {
        Notification::fake();

        $schedule = $this->createSchedule(Carbon::now()->subMinutes(80), 60);

        $shift = $this->clockInToShift($schedule);

        Notification::assertNothingSent();

        (new CronReminders())->handle();

        (new CronReminders())->handle();

        Notification::assertSentToTimes(
            $this->caregiver->user,
            ClockOutReminder::class,
            1
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
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_only_be_notified_once_if_they_have_a_license_expiring_soon()
    {
        Notification::fake();

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->addDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->caregiver->user,
            \App\Notifications\Caregiver\CertificationExpiring::class,
            1
        );
    }

    /** @test */
    public function office_users_should_be_notified_when_a_caregivers_license_is_expiring()
    {
        Notification::fake();

        // create a second office user to another business on the same chain
        $otherBusiness = factory('App\Business')->create();
        $otherBusiness->assignCaregiver($this->caregiver);

        $otherOfficeUser = factory('App\OfficeUser')->create();
        $otherOfficeUser->businesses()->attach($otherBusiness->id);

        // create a third office user to a third business on another chain
        $otherChain = factory('App\BusinessChain')->create();
        $thirdBusiness = factory('App\Business')->create(['chain_id' => $otherChain->id]);
        $thirdBusiness->assignCaregiver($this->caregiver);
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
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $otherOfficeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $thirdOfficeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }

    /** @test */
    public function office_users_should_only_be_notified_of_expiring_licenses_once_per_caregiver()
    {
        Notification::fake();

        $otherBusiness = factory('App\Business')->create();
        $this->officeUser->businesses()->attach($otherBusiness->id);

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->addDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->officeUser->user,
            \App\Notifications\Business\CertificationExpiring::class,
            1
        );
    }

    /** @test */
    public function a_caregiver_should_be_notified_if_they_have_a_license_that_is_expired()
    {
        Notification::fake();

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->subDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        Notification::assertSentTo(
            $this->caregiver->user,
            \App\Notifications\Caregiver\CertificationExpired::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }

    /** @test */
    public function a_caregiver_should_be_notified_once_per_expired_license()
    {
        Notification::fake();

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->subDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->caregiver->user,
            \App\Notifications\Caregiver\CertificationExpired::class,
            1
        );
    }

    /** @test */
    public function office_users_should_only_be_notified_of_expired_licenses_once_per_caregiver()
    {
        Notification::fake();

        $otherBusiness = factory('App\Business')->create();
        $this->officeUser->businesses()->attach($otherBusiness->id);

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->subDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->officeUser->user,
            \App\Notifications\Business\CertificationExpired::class,
            1
        );
    }

    /** @test */
    public function office_users_should_be_notified_when_a_caregivers_license_is_expired()
    {
        Notification::fake();

        // create a second office user to another business on the same chain
        $otherBusiness = factory('App\Business')->create();
        $otherBusiness->chain->assignCaregiver($this->caregiver);
        $otherOfficeUser = factory('App\OfficeUser')->create();
        $otherOfficeUser->businesses()->attach($otherBusiness->id);

        // create a third office user to a third business on another chain
        $otherChain = factory('App\BusinessChain')->create();
        $thirdBusiness = factory('App\Business')->create(['chain_id' => $otherChain->id]);
        $otherChain->assignCaregiver($this->caregiver);
        $thirdOfficeUser = factory('App\OfficeUser')->create();
        $thirdOfficeUser->businesses()->attach($thirdBusiness->id);

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->subDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\CertificationExpired::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $otherOfficeUser->user,
            \App\Notifications\Business\CertificationExpired::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );

        Notification::assertSentTo(
            $thirdOfficeUser->user,
            \App\Notifications\Business\CertificationExpired::class,
            function ($notification) use ($license) {
                return $license->id === $notification->license->id;
            }
        );
    }

    /**
     * @param array $attributes
     * @return \App\Shift
     */
    protected function createShift($attributes = [])
    {
        $attributes += [
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'checked_in_method' => Shift::METHOD_GEOLOCATION,
            'checked_in_time' => Carbon::now()->subHour(),
            'checked_in_number' => null,
            'checked_in_latitude' => null,
            'checked_in_longitude' => null,
            'checked_out_time' => null,
            'checked_out_number' => null,
            'checked_out_latitude' => null,
            'checked_out_longitude' => null,
            'status' => Shift::CLOCKED_IN,
        ];
        return factory(Shift::class)->create($attributes);
    }

    /** @test */
    public function office_users_should_be_notified_for_unverified_clock_ins()
    {
        Notification::fake();

        $this->business->update(['location_exceptions' => true]);
        $schedule = $this->createSchedule(Carbon::now()->subMinutes(25));

        Notification::assertNothingSent();

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->clockIn($schedule);
        $this->assertFalse($shift->checked_in_verified);

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\UnverifiedShift::class,
            function ($notification) use ($shift) {
                return $shift->id === $notification->shift->id;
            }
        );
    }

    /** @test */
    public function office_users_should_not_be_notified_for_verified_clock_ins()
    {
        Notification::fake();

        $this->business->update(['location_exceptions' => true]);
        $schedule = $this->createSchedule(Carbon::now()->subMinutes(25));

        $phone = factory(\App\PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        Notification::assertNothingSent();

        $clockIn = new ClockIn($this->caregiver);
        $shift = $clockIn->setNumber($phone->national_number)->clockIn($schedule);

        $this->assertTrue($shift->checked_in_verified);
        Notification::assertNothingSent();
    }

    /** @test */
    public function office_users_should_be_notified_for_unverified_clock_outs()
    {
        $this->business->update(['location_exceptions' => true]);

        Notification::fake();

        $shift = $this->createShift();

        Notification::assertNothingSent();

        $clockOut = new ClockOut($this->caregiver);
        $clockOut->clockOut($shift);

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\UnverifiedShift::class,
            function ($notification) use ($shift) {
                return $shift->id === $notification->shift->id;
            }
        );
    }

    /** @test */
    public function office_users_should_not_be_notified_for_verified_clock_outs()
    {
        $this->business->update(['location_exceptions' => true]);

        Notification::fake();

        $shift = $this->createShift(['checked_in_verified' => true]);

        $phone = factory(\App\PhoneNumber::class)->make();
        $this->client->phoneNumbers()->save($phone);

        Notification::assertNothingSent();

        $clockIn = new ClockOut($this->caregiver);
        $clockIn->setNumber($phone->national_number)
            ->clockOut($shift);

        $this->assertTrue($shift->checked_out_verified);
        Notification::assertNothingSent();
    }

    /** @test */
    public function office_users_should_be_notified_when_a_caregiver_submits_an_application()
    {
        Notification::fake();

        $application = factory(CaregiverApplication::class)->raw([
            'ssn' => '123-12-1234',
            'address_2' => '',
            'preferred_days' => [1],
            'preferred_times' => [8],
            'preferred_shift_length' => [1],
            'heard_about' => [],
            'caregiver_signature' => 'test',
        ]);

        Notification::assertNothingSent();

        $this->postJson(route('business_chain_routes.apply', ['slug' => $this->business->chain->slug]), $application)
            ->assertStatus(201);

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\ApplicationSubmitted::class,
            function ($notification) use ($application) {
                return $application['email'] === $notification->application->email;
            }
        );
    }

    /** @test */
    public function office_users_should_be_notified_when_a_caregiver_submits_a_timesheet()
    {
        $this->business->update(['allows_manual_shifts' => true]);
        Notification::fake();

        $this->actingAs($this->caregiver->user);

        Notification::assertNothingSent();

        factory('App\Activity', 5)->create([
            'business_id' => $this->business->id,
        ]);
        $this->activities = collect($this->business->allActivities())->pluck('id')->toArray();

        $timesheet = [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [factory('App\TimesheetEntry')->raw(['activities' => $this->activities, 'client_rate' => null, 'caregiver_rate' => null])],
        ];

        $this->postJson(route('timesheets.store'), $timesheet)
            ->assertStatus(200);

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\ManualTimesheet::class,
            function ($notification) use ($timesheet) {
                return $timesheet['caregiver_id'] === $notification->timesheet->caregiver_id;
            }
        );
    }

    /** @test */
    public function office_users_should_receive_notifications_when_caregivers_reply_via_sms()
    {
        Notification::fake();

        $thread = SmsThread::create(array_merge([
            'business_id' => $this->business->id,
            'from_number' => PhoneNumber::formatNational($this->business->outgoing_sms_number),
            'message' => Str::random(10),
            'can_reply' => true,
            'sent_at' => Carbon::now(),
        ]));

        $thread->recipients()->create([
            'user_id' => $this->caregiver->id,
            'number' => $this->caregiver->phoneNumbers()->first()->national_number,
        ]);

        $this->fakeWebhook($this->business->outgoing_sms_number, $this->caregiver);
        $reply = SmsThreadReply::first();

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\NewSmsReply::class,
            function ($notification) use ($reply) {
                return $reply->id === $notification->reply->id;
            }
        );
    }

    /** @test */
    public function office_users_should_be_notified_the_day_of_a_clients_birthday()
    {
        Notification::fake();

        $this->client->user->update(['date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d')]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        Notification::assertSentTo(
            $this->officeUser->user,
            \App\Notifications\Business\ClientBirthday::class,
            function ($notification) {
                return $this->client->id === $notification->client->id;
            }
        );
    }

    /** @test */
    public function trigger_reminders_can_expire()
    {
        $tr = TriggeredReminder::markTriggered(
            \App\Notifications\Business\ClientBirthday::getKey(),
            $this->client->id,
            Carbon::now()->subMinutes(1)
        );

        $this->assertCount(1, TriggeredReminder::all());

        (new CronFlushTriggeredReminders())->handle();

        $this->assertCount(0, TriggeredReminder::all());

        $tr = TriggeredReminder::markTriggered(
            \App\Notifications\Business\ClientBirthday::getKey(),
            $this->client->id,
            Carbon::now()->subDays(1)
        );

        $this->assertCount(1, TriggeredReminder::all());

        (new CronFlushTriggeredReminders())->handle();

        $this->assertCount(0, TriggeredReminder::all());

        $tr = TriggeredReminder::markTriggered(
            \App\Notifications\Business\ClientBirthday::getKey(),
            $this->client->id,
            Carbon::now()->addDays(1)
        );

        $this->assertCount(1, TriggeredReminder::all());

        (new CronFlushTriggeredReminders())->handle();

        $this->assertCount(1, TriggeredReminder::all());
    }

    /** @test */
    public function office_users_should_only_be_notified_the_day_of_a_clients_birthday_once_per_year()
    {
        Notification::fake();

        $this->client->user->update(['date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d')]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->officeUser->user,
            \App\Notifications\Business\ClientBirthday::class,
            1
        );

        // change the current time to the next year
        Carbon::setTestNow(Carbon::now()->addYears(1));

        (new CronFlushTriggeredReminders())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->officeUser->user,
            \App\Notifications\Business\ClientBirthday::class,
            2
        );
    }

    /** @test */
    public function office_users_should_be_notified_once_per_client_birthday()
    {
        Notification::fake();

        $this->client->user->update(['date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d')]);
        $otherUser = factory('App\Client')->create(['business_id' => $this->business->id]);
        $otherUser->user->update(['date_of_birth' => Carbon::now()->subYears(20)->format('Y-m-d')]);

        Notification::assertNothingSent();

        (new CronDailyNotifications())->handle();

        (new CronDailyNotifications())->handle();

        Notification::assertSentToTimes(
            $this->officeUser->user,
            \App\Notifications\Business\ClientBirthday::class,
            2
        );
    }

    /** @test */
    public function by_default_office_users_only_receive_system_notifications()
    {
        Notification::fake();

        // using ClientBirthday here, but functionality is in the BaseNotification
        // class that is used by all notifications.
        $notification = new \App\Notifications\Business\ClientBirthday($this->client);

        $this->assertEquals([SystemChannel::class], $notification->via($this->officeUser->user));
    }

    /** @test */
    public function by_default_caregivers_receive_no_notifications()
    {
        Notification::fake();

        $license = factory(CaregiverLicense::class)->create([
            'expires_at' => Carbon::now()->addDays(3),
            'caregiver_id' => $this->caregiver->id,
        ]);

        // using CertificationExpiring here, but functionality is in the BaseNotification
        // class that is used by all notifications.
        $notification = new \App\Notifications\Caregiver\CertificationExpiring($this->client);

        $this->assertEquals([], $notification->via($this->caregiver->user));
    }

    /** @test */
    public function office_users_should_only_be_notified_via_the_methods_they_are_subscribed_to()
    {
        Notification::fake();

        $this->officeUser->user->update([
            'notification_email' => 'test@test.com',
            'notification_phone' => '1234567890',
            'allow_sms_notifications' => 1,
            'allow_email_notifications' => 1,
            'allow_system_notifications' => 1,
        ]);

        $this->officeUser->notificationPreferences()->delete();
        $this->officeUser->notificationPreferences()->create([
            'key' => \App\Notifications\Business\ClientBirthday::getKey(),
            'sms' => false,
            'email' => false,
            'system' => false,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));

        $this->officeUser->notificationPreferences()
            ->where('key', \App\Notifications\Business\ClientBirthday::getKey())
            ->update([
                'sms' => true,
                'email' => true,
                'system' => true,
            ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([
            SystemChannel::class,
            'mail',
            SmsChannel::class,
        ], $notification->via($this->officeUser->user));
    }

    /** @test */
    public function users_should_not_receive_email_notifications_if_they_are_turned_off()
    {
        Notification::fake();

        $this->officeUser->user->update([
            'notification_email' => 'test@test.com',
            'allow_email_notifications' => 1,
        ]);

        $this->officeUser->notificationPreferences()->delete();
        $this->officeUser->notificationPreferences()->create([
            'key' => \App\Notifications\Business\ClientBirthday::getKey(),
            'sms' => false,
            'email' => true,
            'system' => false,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals(['mail'], $notification->via($this->officeUser->user));

        $this->officeUser->user->update([
            'notification_email' => null,
            'allow_email_notifications' => 1,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));

        $this->officeUser->user->update([
            'notification_email' => 'test@test.com',
            'allow_email_notifications' => 0,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));
    }

    /** @test */
    public function users_should_not_receive_sms_notifications_if_they_are_turned_off()
    {
        Notification::fake();

        $this->officeUser->user->update([
            'notification_phone' => '1234567890',
            'allow_sms_notifications' => 1,
        ]);

        $this->officeUser->notificationPreferences()->delete();
        $this->officeUser->notificationPreferences()->create([
            'key' => \App\Notifications\Business\ClientBirthday::getKey(),
            'sms' => true,
            'email' => false,
            'system' => false,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([SmsChannel::class], $notification->via($this->officeUser->user));

        $this->officeUser->user->update([
            'notification_phone' => null,
            'allow_sms_notifications' => 1,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));

        $this->officeUser->user->update([
            'notification_phone' => '1234567890',
            'allow_sms_notifications' => 0,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));
    }

    /** @test */
    public function users_should_not_receive_system_notifications_if_they_are_turned_off()
    {
        Notification::fake();

        $this->officeUser->user->update([
            'allow_system_notifications' => 1,
        ]);

        $this->officeUser->notificationPreferences()->delete();
        $this->officeUser->notificationPreferences()->create([
            'key' => \App\Notifications\Business\ClientBirthday::getKey(),
            'sms' => false,
            'email' => false,
            'system' => true,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([SystemChannel::class], $notification->via($this->officeUser->user));

        $this->officeUser->user->update([
            'allow_system_notifications' => 0,
        ]);

        $notification = new \App\Notifications\Business\ClientBirthday($this->client);
        $this->assertEquals([], $notification->via($this->officeUser->user));
    }
}
