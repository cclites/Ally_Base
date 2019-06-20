<?php
namespace Tests\Feature;

use App\Business;
use App\Caregiver;
use App\Client;
use App\QuickbooksService;
use App\Schedule;
use App\Shift;
use App\Shifts\ScheduleConverter;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConverterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Shifts\ScheduleConverter
     */
    public $scheduleConverter;

    /**
     * @var \App\Business
     */
    public $business;

    private $client;
    private $caregiver;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();
        $this->scheduleConverter = new ScheduleConverter($this->business);
    }

    public function test_convert_all_between_converts_a_schedule_to_a_shift()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);

        $start = $date->copy()->subHour(); $end = $date->copy()->addHour();
        $convertedShifts = $this->scheduleConverter->convertAllBetween($start, $end);

        $this->assertEquals($schedule->id, $convertedShifts[0]->schedule_id);
    }

    public function test_hasBeenConverted_returns_true_when_converted_at_is_set()
    {
        $schedule = factory(Schedule::class)->create([
            'starts_at' => Carbon::now(),
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'converted_at' => Carbon::now()
        ]);
        $result = $this->scheduleConverter->hasBeenConverted($schedule);
        $this->assertTrue($result);
    }

    public function test_convert_all_between_only_converts_a_schedule_once()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);

        $start = $date->copy()->subHour(); $end = $date->copy()->addHour();

        $firstRun = $this->scheduleConverter->convertAllBetween($start, $end);
        $convertedShifts = $this->scheduleConverter->convertAllBetween($start, $end);
        $this->assertCount(0, $convertedShifts);
    }

    public function test_shiftMatchesTime_returns_true_on_a_similar_shift_within_2_hours()
    {
        $date = Carbon::now($this->business->timezone);
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);
        $shift = factory(Shift::class)->create([
            'schedule_id' => null,
            'checked_in_time' => $date->copy()->subHour()->setTimezone('UTC'),
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);

        $result = $this->scheduleConverter->shiftMatchesTime($schedule, $date);
        $this->assertTrue($result);
    }

    public function test_converted_shift_records_method_converted()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
        ]);

        $start = $date->copy()->subHour(); $end = $date->copy()->addHour();
        $convertedShifts = $this->scheduleConverter->convertAllBetween($start, $end);

        $this->assertEquals(Shift::METHOD_CONVERTED, $convertedShifts[0]->checked_in_method);
        $this->assertEquals(Shift::METHOD_CONVERTED, $convertedShifts[0]->checked_out_method);
    }

    /** @test */
    public function only_schedules_with_ok_status_should_be_converted()
    {
        Carbon::setTestNow(Carbon::parse('2019-02-21 12:00:00'));

        $schedule = factory(Schedule::class)->create([
            'starts_at' => '2019-02-20 12:00:00',
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'status' => Schedule::OPEN_SHIFT,
        ]);

        $convertedShifts = $this->scheduleConverter->convertAllThisWeek();
        $this->assertCount(0, $convertedShifts);

        $schedule->update(['status' => Schedule::OK]);
        $this->assertEquals(Schedule::OK, $schedule->fresh()->status);

        $convertedShifts = $this->scheduleConverter->convertAllThisWeek();
        $this->assertEquals(1, count($convertedShifts));
    }
}
