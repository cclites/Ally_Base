<?php

namespace Tests\Model;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Exceptions\MissingTimezoneException;
use App\Schedule;
use App\ScheduleException;
use App\ScheduleNote;
use Carbon\Carbon;
use DateTime;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ScheduleTest
 *
 *
 * @package Tests\Unit
 */
class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public $businesses;
    public $caregiver;
    public $client;
    public $scheduleAttributes;

    public function setUp() : void
    {
        parent::setUp();
        $this->caregiver = factory(Caregiver::class)->create();
        $this->client = factory(Client::class)->create();
        $this->scheduleAttributes = [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
        ];
    }

    public function testScheduleTestCanBeInitialized()
    {
        $this->assertTrue(true);
    }

    public function testScheduleCanBeCreated()
    {
        $schedule = $this->prepASchedule();
        $this->assertInstanceOf(Schedule::class, $schedule);
    }

    public function testNoteCanBeAttachedToSchedule()
    {
        $schedule = $this->prepASchedule();
        $note = ScheduleNote::create(['note' => 'Hello World']);
        $return = $schedule->attachNote($note);

        $this->assertTrue($return);
        $this->assertInstanceOf(ScheduleNote::class, $schedule->note);
    }

    public function testNoteCanBeAttachedById()
    {
        $schedule = $this->prepASchedule();
        $note = ScheduleNote::create(['note' => 'Hello World']);
        $return = $schedule->attachNote($note->id);

        $this->assertTrue($return);
        $this->assertInstanceOf(ScheduleNote::class, $schedule->note);
    }

    public function testNotesMutatorReturnsAttachedNoteText()
    {
        $schedule = $this->prepASchedule();
        $note = ScheduleNote::create(['note' => 'Hello World']);
        $schedule->attachNote($note);

        $this->assertEquals('Hello World', $schedule->notes);
    }

    public function testNotesMutatorDefaultsToEmptyString()
    {
        $schedule = $this->prepASchedule();
        $this->assertSame('', $schedule->notes);
    }

    public function testScheduleCanHaveActivities()
    {
        $business = factory(Business::class)->create();
        $activity1 = factory(Activity::class)->create(['business_id' => $business->id]);
        $activity2 = factory(Activity::class)->create(['business_id' => $business->id]);
        $schedule = $this->prepASchedule($business->id);

        $schedule->activities()->attach($activity1);
        $schedule->activities()->attach($activity2);

        $this->assertCount(2, $schedule->activities);
    }

    protected function prepASchedule()
    {
        $schedule = factory(Schedule::class)->create($this->scheduleAttributes);
        return $schedule;
    }
}
