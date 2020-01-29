<?php

namespace Tests\Feature;

use App\Caregiver;
use App\Client;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\ShiftFlagsCouldChange;
use App\Schedule;
use Illuminate\Support\Carbon;
use App\Shifts\ScheduleConverter;
use App\Timesheet;
use App\TimesheetEntry;
use App\Activity;
use App\Events\ShiftDeleted;
use App\Billing\ClientRate;
use App\Billing\Service;

class GenerateShiftFlagsTest extends TestCase
{
    use RefreshDatabase;

    protected $caregiver;
    protected $client;
    protected $officeUser;
    protected $business;
    protected $admin;
    protected $service;

    protected function setUp() : void
    {
        parent::setUp();

        $this->admin = factory(\App\Admin::class)->create();
        $this->client = factory(Client::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();

        $this->business = $this->client->business;
        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->chain->assignCaregiver($this->caregiver);
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->business->chain->id]);
        $this->officeUser->businesses()->attach($this->business->id);

        $this->service = factory(Service::class)->create(['chain_id' => $this->client->business->businessChain->id, 'default' => true]);
        factory(ClientRate::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
        ]);
    }

    /**
     * @param $in
     * @param $out
     * @return Shift
     */
    protected function createShift($in, $out)
    {
        return Shift::create($this->makeShift($in, $out));
    }

    /**
     * @param $in
     * @param $out
     * @return Shift
     */
    protected function makeShift($in, $out)
    {
        if (strlen($in) === 8) $in = date('Y-m-d') . ' ' . $in;
        if (strlen($out) === 8) $out = date('Y-m-d') . ' ' . $out;
        
        $data = factory(Shift::class)->raw([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'checked_in_time' => $in,
            'checked_out_time' => $out,
            'hours_type' => 'default',
            'fixed_rates' => 1,
            'mileage' => 0,
            'other_expenses' => 0,
            'service_id' => $this->service->id,
        ]);

        return $data;
    }

    public function createSchedule($starts_at = null)
    {
        if (empty($starts_at)) {
            $starts_at = Carbon::now();
        }

        $data = factory(Schedule::class)->create([
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'business_id' => $this->client->business_id,
            'starts_at' => $starts_at,
            'weekday' => $starts_at->format('w'),
        ]);

        return $data;
    }
    
    /**
     * @test
     */
    public function it_triggers_when_an_office_user_creates_a_shift()
    {
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $this->actingAs($this->officeUser->user);

        $data = $this->makeShift('12:00:00', '18:00:00');

        $result = $this->postJson(route('business.shifts.store'), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function it_triggers_when_the_schedule_convert_is_run()
    {
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $schedule = $this->createSchedule(Carbon::now()->subDays(1));

        $converter = new ScheduleConverter($this->business);
        $converter->convertAllThisWeek();
    }

    /** @test */
    public function it_triggers_when_a_timesheet_converts_to_shifts()
    {
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $timesheet = factory(Timesheet::class)->create();    
        $entry = factory(TimesheetEntry::class)->create(['timesheet_id' => $timesheet->id]);

        $timesheet->createShiftsFromEntries();
    }

    protected function telefonyPost($url, $parameters = [], $headers = [])
    {
        $url = rtrim('/api/telefony/' . $url, '/');
        $parameters += ['From' => '1234567890'];
        $headers += ['Content-Type' => 'text/xml'];
        return $this->post($url, $parameters, $headers);
    }

    /** @test */
    public function it_triggers_when_an_admin_imports_shifts()
    {
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $this->actingAs($this->admin->user);

        $shift1 = $this->makeShift('01:00:00', '02:00:00');
        $shift1['caregiver_rate'] = '30.00';
        $shift1['provider_fee'] = '1.00';

        $data = [
            'name' => substr($this->caregiver->name, 0, 15),
            'shifts' => [$shift1],
        ];

        $this->postJson(route('admin.imports.store'), $data)
            ->assertStatus(201);
    }

    /** @test */
    public function it_triggers_on_telefony_checkout()
    {
        $shift = $this->createShift('12:00:00', '18:00:00');
        $activity = factory(Activity::class)->create();
        $shift->activities()->attach($activity->id);
        $shift->update(['status' => Shift::CLOCKED_IN, 'checked_out_time' => null]);

        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $phone = factory(\App\PhoneNumber::class)->make(['national_number' => '1234567890']);
        $this->client->phoneNumbers()->save($phone);

        $response = $this->telefonyPost('check-out/finalize/' . $shift->id);
        $response->assertSee('You have successfully clocked out.');
        $this->assertFalse($shift->fresh()->statusManager()->isClockedIn());
    }

    /** @test */
    public function it_triggers_when_an_office_user_performs_an_office_clock_out()
    {
        $this->actingAs($this->officeUser->user);
        
        $shift = $this->createShift('12:00:00', '18:00:00');
        $data = [
            'checked_in_time' => $shift->checked_in_time->toDateTimeString(), 
            'checked_out_time' => $shift->checked_out_time->toDateTimeString()
        ];
        $shift->update(['status' => Shift::CLOCKED_IN, 'checked_out_time' => null]);
        
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $this->postJson(route('business.shifts.clockout', ['shift' => $shift]), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function it_triggers_when_a_caregiver_clocks_out()
    {
        $this->actingAs($this->caregiver->user);
        
        $shift = $this->createShift('12:00:00', '18:00:00');
        $shift->update(['status' => Shift::CLOCKED_IN, 'checked_out_time' => null]);
        $data = [
            'caregiver_comments' => $shift->caregiver_comments,
            'mileage' => $shift->mileage,
            'other_expenses' => $shift->other_expenses,
            'other_expenses_desc' => $shift->other_expenses_desc,
            'latitude' => $shift->latitude,
            'longitude' => $shift->longitude,
            'goals' => $shift->goals,
            'questions' => $shift->questions,
            'narrative_notes' => $shift->narrative_notes,
        ];
        
        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $this->postJson("/clock-out/{$shift->id}", $shift->toArray())
            ->assertStatus(200);
    }

    /** @test */
    public function it_triggers_when_an_office_user_updates_a_shift()
    {
        $this->actingAs($this->officeUser->user);
        
        $shift = $this->createShift('12:00:00', '18:00:00');
        $shift->update(['status' => Shift::CLOCKED_OUT]);
        
        $activity = factory(Activity::class)->create();
        $shift->activities()->attach($activity->id);

        $this->expectsEvents(ShiftFlagsCouldChange::class);

        $data = $shift->toArray();
        $data['activities'] = [$activity->id];

        $this->patchJson(route('business.shifts.update', ['shift' => $shift]), $shift->toArray())
            ->assertStatus(200);
    }

    /** @test */
    public function it_triggers_when_a_client_modifies_a_shift()
    {
        $this->actingAs($this->client->user);
        
        $shift = $this->createShift('12:00:00', '18:00:00');
        $shift->update(['status' => Shift::CLOCKED_OUT]);
        
        $this->expectsEvents(ShiftFlagsCouldChange::class);
        
        $data = [
            'checked_in_time' => $shift->checked_in_time->toDateTimeString(), 
            'checked_out_time' => $shift->checked_out_time->toDateTimeString()
        ];

        // make sure business allows this method 
        app('settings')->set($this->client->role->business_id, 'allow_client_confirmations', 1);

        $this->patchJson(route('client.unconfirmed-shifts.update', ['shift' => $shift->id]), $data)
            ->assertStatus(200);
    }

    /** @test */
    public function duplicate_shift_flags_should_trigger_when_an_office_user_deletes_a_shift()
    {
        $this->actingAs($this->officeUser->user);
        
        $shift = $this->createShift('12:00:00', '18:00:00');
        $shift->update(['status' => Shift::CLOCKED_OUT]);

        $this->expectsEvents(ShiftDeleted::class);
        
        $this->deleteJson(route('business.shifts.destroy', ['shift' => $shift]))
            ->assertStatus(200);
    }
}
