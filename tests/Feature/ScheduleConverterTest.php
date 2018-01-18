<?php
namespace Tests\Feature;

use App\Business;
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

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->scheduleConverter = new ScheduleConverter($this->business);
    }

    public function test_convert_all_between_converts_a_schedule_to_a_shift()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => 1,
            'caregiver_id' => 1,
        ]);

        $start = $date->copy()->subHour(); $end = $date->copy()->addHour();
        $convertedShifts = $this->scheduleConverter->convertAllBetween($start, $end);

        $this->assertEquals($schedule->id, $convertedShifts[0]->schedule_id);
    }

    public function test_hasBeenConverted_returns_true_when_a_shift_has_the_schedule_id()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => 1,
            'caregiver_id' => 1,
        ]);
        factory(Shift::class)->create(['schedule_id' => $schedule->id]);

        $result = $this->scheduleConverter->hasBeenConverted($schedule);
        $this->assertTrue($result);
    }

    public function test_convert_all_between_only_converts_a_schedule_once()
    {
        $date = Carbon::now();
        $schedule = factory(Schedule::class)->create([
            'starts_at' => $date,
            'business_id' => $this->business->id,
            'client_id' => 1,
            'caregiver_id' => 1,
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
            'client_id' => 1,
            'caregiver_id' => 1,
        ]);
        $shift = factory(Shift::class)->create([
            'schedule_id' => null,
            'checked_in_time' => $date->copy()->subHour()->setTimezone('UTC'),
            'business_id' => $this->business->id,
            'client_id' => 1,
            'caregiver_id' => 1,
        ]);

        $result = $this->scheduleConverter->shiftMatchesTime($schedule, $date);
        $this->assertTrue($result);
    }


}