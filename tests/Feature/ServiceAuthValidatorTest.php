<?php

namespace Tests\Feature;

use App\Billing\ScheduleService;
use App\Schedule;
use App\Shift;
use App\Shifts\ServiceAuthValidator;
use Tests\CreatesBusinesses;
use Tests\CreatesClientAuthorizations;
use Tests\CreatesSchedules;
use Tests\CreatesShifts;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\ClientRate;
use App\Billing\Service;
use Carbon\Carbon;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\ClientAuthorization;
use App\Billing\Payer;

class ServiceAuthValidatorTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesSchedules, CreatesShifts;
    use CreatesClientAuthorizations;

    /**
     * @var \App\Service
     */
    protected $service;

    /**
     * @var \App\Shifts\ServiceAuthValidator
     */
    protected $validator;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->service = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => true
        ]);

        $this->validator = new ServiceAuthValidator($this->client);
    }

    public function assertExceedsServiceAuth(object $shiftOrSchedule) : void
    {
        if ($shiftOrSchedule instanceof Shift) {
            $this->assertNotNull($this->validator->shiftExceedsServiceAuthorization($shiftOrSchedule));
        } elseif ($shiftOrSchedule instanceof Schedule) {
            $this->assertNotNull($this->validator->scheduleExceedsServiceAuthorization($shiftOrSchedule));
        } else {
            $this->assertTrue(false, 'Invalid parameter to assertExceedsServiceAuth');
        }
    }

    public function assertDoesNotExceedServiceAuth(object $shiftOrSchedule) : void
    {
        if ($shiftOrSchedule instanceof Shift) {
            $this->assertNull($this->validator->shiftExceedsServiceAuthorization($shiftOrSchedule));
        } elseif ($shiftOrSchedule instanceof Schedule) {
            $this->assertNull($this->validator->scheduleExceedsServiceAuthorization($shiftOrSchedule));
        } else {
            $this->assertTrue(false, 'Invalid parameter to assertExceedsServiceAuth');
        }
    }

    /**
     * =============================================================================
     * SHIFTS
     * =============================================================================
     */

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 7);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $shift2 = $this->createShift(Carbon::today(), '12:00:00', 6);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_a_service_breakout_shift_exceeds_clients_max_weekly_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 7);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $shift2 = $this->createServiceBreakoutShift(Carbon::today(), '12:00:00', [
            $this->service->id, $this->service->id, $this->service->id
        ], 2);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function a_shift_should_exceed_clients_max_weekly_hours_based_on_the_period_the_shift_takes_place()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::parse('last monday'), '11:00:00', 7);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $shift2 = $this->createShift(Carbon::parse('last monday'), '12:00:00', 6);
        $this->assertTrue($this->validator->shiftExceedsMaxClientHours($shift2));
    }

    /** @test */
    public function it_fails_when_an_actual_hours_shift_exceeds_weekly_auth_limits()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 7);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift2 = $this->createShift(Carbon::today(), '12:00:00', 6);
        $this->assertExceedsServiceAuth($shift2);
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_monthly_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_MONTHLY,
        ]);

        $shift = $this->createShift(Carbon::now()->startOfMonth(), '11:00:00', 7);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift2 = $this->createShift(Carbon::today(), '12:00:00', 6);
        $this->assertExceedsServiceAuth($shift2);
    }

    /** @test */
    public function it_fails_when_actual_hours_shift_exceeds_daily_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_DAILY,
        ]);

        $shift = $this->createShift(Carbon::yesterday(), '10:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift2 = $this->createShift(Carbon::today(), '10:00:00', 5.5);
        $this->assertExceedsServiceAuth($shift2);
    }

    /** @test */
    public function it_should_not_fail_for_shifts_outside_the_service_auth_period()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift = $this->createShift(Carbon::now()->startOfWeek(), '11:00:00', 7);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift(Carbon::now()->startOfWeek()->subDays(3), '12:00:00', 6);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift(Carbon::now(), '12:00:00', 6);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function a_service_breakout_shift_can_fail_based_on_a_specific_service_type()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);
        $otherService = factory(Service::class)->create(['chain_id' => $this->client->business->businessChain->id, 'default' => false]);

        // shift with only 3 hours of specified service id should not flag yet
        $shift = $this->createServiceBreakoutShift(Carbon::today(), '11:00:00', [$this->service->id, $otherService->id], 3);
        $this->assertEquals(3, $shift->getBillableHours($this->service->id));
        $this->assertDoesNotExceedServiceAuth($shift);

        // a second shift with 3 more hours of specified service id should flag
        $shift = $this->createServiceBreakoutShift(Carbon::today(), '11:00:00', [$this->service->id, $otherService->id], 3);
        $this->assertEquals(3, $shift->getBillableHours($this->service->id));
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function a_service_breakout_shift_can_fail_on_any_payer()
    {
        $payer = factory(Payer::class)->create();
        $otherPayer = factory(Payer::class)->create();

        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 6, ['service_id' => null]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $payer->id,
        ]);
        factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'duration' => 3,
            'service_id' => $this->service->id,
            'payer_id' => $otherPayer->id,
        ]);
        $shift = $shift->fresh();

        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function fixed_hour_shifts_should_fail_if_exceeding_service_auth_fixed_limit()
    {
        $this->createClientAuth([
            'units' => 2,
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 2, [
            'fixed_rates' => 1
        ]);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift(Carbon::today(), '03:00:00', 1, [
            'fixed_rates' => 1
        ]);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift(Carbon::today(), '04:00:00', 1, [
            'fixed_rates' => 1
        ]);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function it_should_only_fail_for_auths_effective_during_the_time_of_the_shift()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subYears(1)->toDateString(),
            'effective_end' => Carbon::today()->subDays(1)->toDateString(),
        ]);

        $shift = $this->createShift(Carbon::now(), '11:00:00', 7);
        $this->assertCount(0, $this->client->getActiveServiceAuths($shift->checked_in_time));
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift(Carbon::today()->subMonths(2), '11:00:00', 7);
        $this->assertCount(1, $this->client->getActiveServiceAuths($shift->checked_in_time));
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function it_fails_based_on_the_period_the_shift_takes_place()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $date = Carbon::now()->subMonth(1)->startOfWeek();

        $shift = $this->createShift($date, '11:00:00', 7);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift = $this->createShift($date->copy()->addDays(2), '12:00:00', 6);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function an_actual_hours_shift_should_fail_when_hours_exceed_full_term_limits()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_TERM,
            'effective_start' => Carbon::now()->subDays(5)->toDateString(),
            'effective_end' => Carbon::now()->addYears(1)->toDateString(),
        ]);

        // 4 hour shift inside the term dates
        $shift = $this->createShift(Carbon::yesterday(), '10:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($shift);

        // shift should exceed the max unit but is outside the term dates
        $shift = $this->createShift(Carbon::now()->addYears(2), '10:00:00', 2.5);
        $this->assertDoesNotExceedServiceAuth($shift);

        // shift exceeds the max units and is inside the term dates - flag
        $shift = $this->createShift(Carbon::now()->addMonths(2), '10:00:00', 2.5);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function an_actual_hours_shift_should_fail_when_hours_exceeds_specific_daily_limits()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 5,
            'tuesday' => 500,
        ]);

        // 4 hours shift on a monday -> no flag yet
        $shift = $this->createShift(Carbon::parse('last monday'), '10:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($shift);

        // 2 hour shift on any other day -> still no flag
        $shift = $this->createShift(Carbon::parse('last tuesday'), '14:01:00', 2);
        $this->assertDoesNotExceedServiceAuth($shift);

        // 2 hours shift on the same monday -> should flag
        $shift = $this->createShift(Carbon::parse('last monday'), '14:01:00', 2);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function a_spec_day_auth_should_always_fail_on_days_marked_0()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 0,
        ]);

        $shift = $this->createShift(Carbon::parse('last monday'), '10:00:00', 1);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function it_fails_on_all_days_an_actual_hours_shift_extends_to()
    {
        $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 500,
            'tuesday' => 3,
        ]);

        // create a shift that is 1 hour on monday and 4 hours on tuesday
        $shift = $this->createShift(Carbon::parse('last monday'), '23:00:00', 5);
        $this->assertEquals(4, $shift->getBillableHoursForDay(Carbon::parse('last tuesday')));
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function a_service_breakout_shift_should_count_total_service_hours_for_all_days_of_the_shift()
    {
        $auth = $this->createClientAuth([
            'units' => 0.0,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 3,
            'tuesday' => 500,
        ]);

        // shift with only 4 hours should not flag yet
        $shift = $this->createServiceBreakoutShift(
            Carbon::parse('last monday'),
            '23:00:00',
            [$this->service->id, $this->service->id],
            4
        );

        // shift should flag because 4 > 3 on monday
        $this->assertEquals(8, $shift->getBillableHoursForDay(Carbon::parse('last monday'), $this->service->id));
        $this->assertEquals(8, $shift->getBillableHoursForDay(Carbon::parse('last tuesday'), $this->service->id));
        $this->assertExceedsServiceAuth($shift);

        // auth should flag because same amount of hours on tuesday
        $auth->update([
            'monday' => 500,
            'tuesday' => 3,
        ]);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function a_fixed_unit_type_client_auth_should_count_for_all_dates_a_shift_expands_to()
    {
        $this->createClientAuth([
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_SPECIFIC_DAYS,
            'monday' => 2,
            'tuesday' => 1,
        ]);

        // create shift that expands from monday - tuesday
        $monday = Carbon::parse('last monday');
        $shift = $this->createShift($monday, '23:00:00', 5, [
            'fixed_rates' => 1,
        ]);

        // no flag yet
        $this->assertDoesNotExceedServiceAuth($shift);

        // create another shift on tuesday
        $shift = $this->createShift($monday->copy()->addDays(1), '06:00:00', 3, [
            'fixed_rates' => 1,
        ]);

        // should flag because tuesday now has 2
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function it_can_fail_for_service_auths_that_are_effective_on_the_second_day_of_the_shift()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_DAILY,
            'effective_start' => Carbon::tomorrow()->toDateString(),
        ]);

        $this->assertCount(0, $this->client->getActiveServiceAuths(Carbon::today()));
        $this->assertCount(1, $this->client->getActiveServiceAuths(Carbon::tomorrow()));

        $shift = $this->createShift(Carbon::today(), '23:00:00', 8);
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    function it_should_count_the_hours_of_fixed_shifts_when_checking_non_fixed_auths()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $shift1 = $this->createShift(Carbon::parse('last tuesday'), '01:00:00', 4);
        $shift2 = $this->createShift(Carbon::parse('last tuesday')->addDays(1), '01:00:00', 4, [
            'fixed_rates' => 1,
        ]);

        $this->assertExceedsServiceAuth($shift2);
    }

    /**
     * =============================================================================
     * SCHEDULES
     * =============================================================================
     */

    /** @test */
    function it_can_fail_based_on_an_actual_hour_schedule_exceeding_max_client_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', 2);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 5);
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule));
    }

    /** @test */
    function it_can_fail_based_on_a_service_breakout_schedule_exceeding_max_client_hours()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', 2);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule2 = $this->createServiceBreakoutSchedule(
            Carbon::now(),
            '12:00:00',
            [$this->service->id, $this->service->id, $this->service->id],
            2
        );
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule2));
    }

    /** @test */
    function it_can_fail_based_on_a_schedule_that_has_not_yet_persisted()
    {
        $this->client->update(['max_weekly_hours' => 10]);

        $shift = $this->createShift(Carbon::today(), '01:00:00', 2);
        $this->assertFalse($this->validator->shiftExceedsMaxClientHours($shift));

        $schedule = $this->createSchedule(Carbon::today(), '03:00:00', 4);
        $this->assertFalse($this->validator->scheduleExceedsMaxClientHours($schedule));

        $schedule = Schedule::make($this->makeSchedule(Carbon::today(), '03:00:00', 5));
        $this->assertTrue($this->validator->scheduleExceedsMaxClientHours($schedule));
    }

    /** @test */
    function it_can_failed_if_a_persisted_schedule_exceeds_the_weekly_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');

        $schedule = $this->createSchedule($tuesday, '01:00:00', 3);
        $this->assertDoesNotExceedServiceAuth($schedule);

        $schedule2 = $this->createSchedule($tuesday->addDays(2), '01:00:00', 3);
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function a_schedule_should_fail_based_on_existing_shifts()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');

        $shift = $this->createShift($tuesday, '01:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($shift);

        $schedule = $this->createSchedule($tuesday->copy()->addDays(1), '01:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($schedule);

        $schedule2 = $this->createSchedule($tuesday->addDays(2), '01:00:00', 5);
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_not_include_schedules_that_have_been_converted_to_shifts()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $schedule = $this->createSchedule($tuesday, '01:00:00', 6);

        // creates shift from schedule that is only 2 hours long
        $shift = $this->createShift($tuesday, '01:00:00', 2);
        $shift->update(['schedule_id' => $schedule->id]);

        $schedule2 = $this->createSchedule($tuesday->addDays(2), '01:00:00', 6);

        // should pass because 2 + 6 = 8 (ignores first schedule entry)
        $this->assertDoesNotExceedServiceAuth($schedule2);
    }

    /** @test */
    function it_fails_when_a_non_persisted_schedule_exceeds_weekly_service_auth_limits()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $this->createShift($tuesday, '01:00:00', 4);

        $schedule = $this->createSchedule($tuesday->copy()->addDays(1), '01:00:00', 4);
        $this->assertDoesNotExceedServiceAuth($schedule);

        $data = $this->makeSchedule($tuesday->addDays(2), '01:00:00', 5);
        $schedule2 = Schedule::make($data);

        $this->assertCount(1, Schedule::all());
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_check_the_schedule_model_values_over_the_persisted_data()
    {
        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $schedule = $this->createSchedule($tuesday, '01:00:00', 5);
        $this->assertDoesNotExceedServiceAuth($schedule);

        $schedule2 = $this->createSchedule($tuesday->addDays(1), '01:00:00', 3);
        $this->assertDoesNotExceedServiceAuth($schedule2);

        $schedule2->duration = 7 * 60;
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_check_the_schedule_model_services_over_the_persisted_data()
    {
        $this->createClientAuth([
            'units' => 8,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $schedule = $this->createServiceBreakoutSchedule($tuesday, '01:00:00', [$this->service->id], 5);
        $this->assertDoesNotExceedServiceAuth($schedule->fresh());

        $schedule2 = $this->createServiceBreakoutSchedule($tuesday->addDays(1), '01:00:00', [$this->service->id], 1);
        $this->assertDoesNotExceedServiceAuth($schedule2->fresh());

        $schedule2 = $schedule2->fresh()->load('services');
        $schedule2->services->first()->duration = 5;
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_fail_when_a_persisted_fixed_hours_schedule_exceeds_service_limits()
    {
        $this->createClientAuth([
            'units' => 2,
            'unit_type' => ClientAuthorization::UNIT_TYPE_FIXED,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $notFixedShift = $this->createShift($tuesday, '06:00:00', 4);
        $shift = $this->createShift($tuesday, '01:00:00', 4, ['fixed_rates' => 1]);

        $schedule = $this->createSchedule($tuesday->copy()->addDays(1), '01:00:00', 4, [
            'fixed_rates' => 1
        ]);
        $this->assertDoesNotExceedServiceAuth($schedule);

        $schedule2 = $this->createSchedule($tuesday->copy()->addDays(1), '01:00:00', 5, [
            'fixed_rates' => 1
        ]);
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_count_the_hours_of_fixed_schedules_when_checking_non_fixed_auths()
    {
        $this->createClientAuth([
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
        ]);

        $tuesday = Carbon::parse('last tuesday');
        $schedule1 = $this->createSchedule($tuesday, '01:00:00', 4);
        $schedule2 = $this->createSchedule($tuesday->addDays(1), '01:00:00', 4, [
            'fixed_rates' => 1
        ]);

        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    function it_should_check_service_auths_for_all_days_an_actual_hours_schedule_exists_on()
    {
        $month1 = Carbon::parse('05/01/2019', $this->client->getTimezone())->startOfMonth();
        $month2 = Carbon::parse('06/01/2019', $this->client->getTimezone())->startOfMonth();

        $this->createClientAuth([
            'units' => 9,
            'effective_start' => $month2->toDateString(),
        ]);

        $schedule1 = $this->createSchedule($month2->copy()->addDays(1), '01:00:00', 5);
        $this->assertDoesNotExceedServiceAuth($schedule1);

        $schedule2 = $this->createSchedule($month1->copy()->endOfMonth(), '22:00:00', 5);
        $this->assertDoesNotExceedServiceAuth($schedule2);

        $schedule3 = $this->createSchedule($month1->copy()->endOfMonth(), '22:00:00', 5);
        $this->assertExceedsServiceAuth($schedule3);
    }

    /** @test */
    function it_should_count_all_service_breakout_hours_when_a_schedule_spans_multiple_days()
    {
        $month1 = Carbon::parse('last month', $this->client->getTimezone())->startOfMonth();
        $month2 = Carbon::parse('this month', $this->client->getTimezone())->startOfMonth();

        $this->createClientAuth([
            'units' => 9,
            'effective_start' => $month2->toDateString(),
        ]);

        $services = [$this->service->id, $this->service->id, $this->service->id, $this->service->id, $this->service->id];
        $schedule1 = $this->createServiceBreakoutSchedule($month2->copy()->addDays(1), '01:00:00', $services, 1);
        $this->assertDoesNotExceedServiceAuth($schedule1);

        $schedule2 = $this->createServiceBreakoutSchedule($month1->copy()->endOfMonth(), '22:00:00', $services, 1);
        $this->assertExceedsServiceAuth($schedule2);
    }

    /** @test */
    public function weekly_auth_periods_should_calculate_shifts_based_on_their_week_start_value()
    {
        Carbon::setTestNow(Carbon::parse('2019-06-10 12:00:00')); // today is Monday

        $authorization = $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'week_start' => 2, // Tuesday
            'effective_start' => Carbon::yesterday()->format('Ymd'),
        ]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 5);
        $this->createShift(Carbon::tomorrow(), '12:00:00', 6);

        $this->assertDoesNotExceedServiceAuth($shift);

        $authorization->update(['week_start' => 1]); // Set weeks to start on Monday
        $this->assertExceedsServiceAuth($shift);
    }

    /** @test */
    public function weekly_auth_periods_should_calculate_schedules_based_on_their_week_start_value()
    {
        Carbon::setTestNow(Carbon::parse('2019-06-10 12:00:00')); // today is Monday

        $authorization = $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'week_start' => 2, // Tuesday
            'effective_start' => Carbon::yesterday()->format('Ymd'),
        ]);

        $schedule = $this->createSchedule(Carbon::today(), '11:00:00', 5);
        $this->createSchedule(Carbon::tomorrow(), '11:00:00', 6);

        $this->assertDoesNotExceedServiceAuth($schedule);

        $authorization->update(['week_start' => 1]); // Set weeks to start on Monday
        $this->assertExceedsServiceAuth($schedule);
    }

    /** @test */
    public function running_a_weekly_period_check_should_not_alter_the_global_carbon_start_of_week_value()
    {
        $this->assertEquals(Carbon::MONDAY, Carbon::getWeekStartsAt());
        $this->assertEquals(Carbon::SUNDAY, Carbon::getWeekEndsAt());

        Carbon::setTestNow(Carbon::parse('2019-06-10 12:00:00')); // today is Monday

        $this->createClientAuth([
            'units' => 10,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'week_start' => 2, // Tuesday
            'effective_start' => Carbon::yesterday()->format('Ymd'),
        ]);

        $shift = $this->createShift(Carbon::today(), '11:00:00', 5);
        $this->assertDoesNotExceedServiceAuth($shift);

        $this->assertEquals(Carbon::MONDAY, Carbon::getWeekStartsAt());
        $this->assertEquals(Carbon::SUNDAY, Carbon::getWeekEndsAt());
    }

    /** @test */
    public function if_a_weekly_auth_ends_in_the_middle_of_the_week_it_should_not_calculate_the_whole_week()
    {
        $auth = $this->createClientAuth([
            'units' => 6,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'week_start' => 1, // Monday
            'effective_start' => Carbon::parse('2019-09-01'),
        ]);

        $shift = $this->createShift(Carbon::parse('2019-09-30'), '11:00:00', 5);
        $this->assertDoesNotExceedServiceAuth($shift);

        $shift2 = $this->createShift(Carbon::parse('2019-10-01'), '11:00:00', 5);
        $this->assertExceedsServiceAuth($shift->fresh());

        $auth->update(['effective_end' => Carbon::parse('2019-09-30')]);

        $this->assertDoesNotExceedServiceAuth($shift->fresh());
    }

    /** @test */
    public function it_should_only_fail_for_auths_of_the_same_service_as_the_shift()
    {
        // This represents an issue where we were looking at all active service auths instead of
        // the ones matching the shift's services.  This resulted in false positives if any of
        // the auths failed, even if it was not for the service on the shift.
        $otherService = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => false
        ]);

        $this->createClientAuth([
            'service_id' => $otherService,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subDays(1)->toDateString(),
            'effective_end' => Carbon::today()->addDays(1)->toDateString(),
        ]);

        $this->createClientAuth([
            'service_id' => $this->service->id,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subDays(1)->toDateString(),
            'effective_end' => Carbon::today()->addDays(1)->toDateString(),
        ]);

        $shift = $this->createShift(Carbon::now(), '11:00:00', 7, ['service_id' => $otherService->id]);
        $this->assertCount(1, $this->client->getActiveServiceAuths($shift->checked_in_time, $shift->getServices()->pluck('id')));
        $this->assertExceedsServiceAuth($shift);

        $shift2 = $this->createShift(Carbon::now(), '11:00:00', 3, ['service_id' => $this->service->id]);
        $this->assertCount(1, $this->client->getActiveServiceAuths($shift2->checked_in_time, $shift2->getServices()->pluck('id')));
        $this->assertCount(2, $this->client->getActiveServiceAuths($shift2->checked_in_time));
        $this->assertDoesNotExceedServiceAuth($shift2);
    }

    /** @test */
    public function it_should_only_fail_for_auths_of_the_same_service_as_the_schedule()
    {
        $otherService = factory(Service::class)->create([
            'chain_id' => $this->client->business->businessChain->id,
            'default' => false
        ]);

        $this->createClientAuth([
            'service_id' => $otherService,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subDays(1)->toDateString(),
            'effective_end' => Carbon::today()->addDays(1)->toDateString(),
        ]);

        $this->createClientAuth([
            'service_id' => $this->service->id,
            'units' => 5,
            'period' => ClientAuthorization::PERIOD_WEEKLY,
            'effective_start' => Carbon::today()->subDays(1)->toDateString(),
            'effective_end' => Carbon::today()->addDays(1)->toDateString(),
        ]);

        $schedule = $this->createSchedule(Carbon::now(), '11:00:00', 7, ['service_id' => $otherService->id]);
        $this->assertExceedsServiceAuth($schedule);

        $schedule2 = $this->createSchedule(Carbon::now(), '11:00:00', 3, ['service_id' => $this->service->id]);
        $this->assertDoesNotExceedServiceAuth($schedule2);
    }
}