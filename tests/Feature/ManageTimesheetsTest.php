<?php

namespace Tests\Feature;

use Tests\CreatesBusinesses;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Events\TimesheetCreated;

class ManageTimesheetsTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    public function setUp()
    {
        parent::setUp();

        $this->disableExceptionHandling();

        $this->createBusinessWithUsers();

        $this->business->update(['allows_manual_shifts' => true]);
        factory('App\Activity', 5)->create([
            'business_id' => $this->business->id,
        ]);
        $this->activities = collect($this->business->allActivities())->pluck('id')->toArray();
    }

    public function generateEntry()
    {
        $duration = mt_rand(60, 720);
        $start = date('Y-m-d H:i:s', time() - mt_rand(1200, 86400*90));
        $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $duration . ' minutes'));
        
        return [
            'mileage' => 0.0,
            'other_expenses' => 0.0,
            'checked_in_time' => $start,
            'checked_out_time' => $end,
            'activities' => $this->activities,
            'caregiver_comments' => 'test',
        ];
    }

    public function createTimesheet()
    {
        $timesheet = factory('App\Timesheet')->create([
            'client_id' => $this->client->id,
            'business_id' => $this->business->id,
            'caregiver_id' => $this->caregiver->id,
            'creator_id' => $this->caregiver->id,
        ]);

        $entry = factory('App\TimesheetEntry')->create([
            'timesheet_id' => $timesheet->id,
        ]);

        $entry->activities()->sync($this->activities);

        return $timesheet;
    }

    /** @test */
    public function a_cg_can_access_the_create_timesheet_form()
    {
        $this->actingAs($this->caregiver->user);

        $this->get(route('timesheets.create'))
            ->assertStatus(200)
            ->assertSee('Submit Timesheet');
    }

    /** @test */
    public function a_cg_can_submit_a_timesheet()
    {
        $this->actingAs($this->caregiver->user);

        $this->assertCount(0, $this->business->timesheets);
        
        $this->post(route('timesheets.store'), [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [$this->generateEntry(), $this->generateEntry()],
        ])->assertStatus(200);
        
        $this->assertCount(1, $this->business->fresh()->timesheets);
        $this->assertCount(2, $this->business->fresh()->timesheets()->first()->entries);
    }

    /** @test */
    public function a_cg_cannot_create_a_timesheet_if_the_business_doesnt_allow()
    {
        $this->actingAs($this->caregiver->user);

        $this->business->update(['allows_manual_shifts' => false]);

        $this->assertCount(0, $this->business->timesheets);
        
        $this->post(route('timesheets.store'), [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [$this->generateEntry()],
        ])->assertStatus(403);
        
        $this->assertCount(0, $this->business->fresh()->timesheets);
    }

    /** @test */
    public function when_a_cg_submits_a_timesheet_it_should_trigger_an_exception_event()
    {
        \Event::fake();

        $this->actingAs($this->caregiver->user);

        $result = $this->post(route('timesheets.store'), [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [$this->generateEntry()],
        ])->assertStatus(200);
        
        $cg = $this->caregiver->id;

        \Event::assertDispatched(TimesheetCreated::class, function ($e) use ($cg) {
            return $e->timesheet->caregiver->id === $cg;
        });
    }

    /** @test */
    public function a_business_can_create_a_timesheet()
    {
        $this->actingAs($this->officeUser->user);

        $this->assertCount(0, $this->business->timesheets);

        $this->post(route('business.timesheet.store'), [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [$this->generateEntry()],
        ])->assertStatus(200);
        
        $this->assertCount(1, $this->business->fresh()->timesheets);
    }

    /** @test */
    public function a_business_can_approve_a_caregivers_timesheet_to_create_shifts()
    {
        $this->actingAs($this->officeUser->user);

        $timesheet = $this->createTimesheet();

        $this->assertCount(1, $this->business->fresh()->timesheets);
        $this->assertCount(0, $this->business->fresh()->shifts);
        
        $data = $timesheet->fresh()->toArray();

        $this->post(route('business.timesheet.update', ['timesheet' => $timesheet]) . '?approve=1', $data)
            ->assertStatus(200);
        
        $this->assertCount(1, $this->business->fresh()->timesheets);
        $this->assertCount(1, $this->business->fresh()->shifts);
        $this->assertTrue($timesheet->fresh()->isApproved);
    }

    /** @test */
    public function a_business_can_save_a_timesheet_and_create_shifts()
    {
        $this->actingAs($this->officeUser->user);

        $this->assertCount(0, $this->business->timesheets);
        $this->assertCount(0, $this->business->shifts);

        $this->post(route('business.timesheet.store') . '?approve=1', [
            'caregiver_id' => $this->caregiver->id,
            'client_id' => $this->client->id,
            'entries' => [$this->generateEntry(), $this->generateEntry(), $this->generateEntry()],
        ])->assertStatus(200);
        
        $this->assertCount(1, $this->business->fresh()->timesheets);
        $this->assertCount(3, $this->business->fresh()->shifts);
    }

    /** @test */
    public function a_business_can_deny_a_caregivers_timesheet()
    {
        $this->actingAs($this->officeUser->user);

        $timesheet = $this->createTimesheet();

        $this->assertCount(1, $this->business->fresh()->timesheets);
        $this->assertCount(0, $this->business->fresh()->shifts);
        
        $data = $timesheet->fresh()->toArray();

        $this->post(route('business.timesheet.deny', ['timesheet' => $timesheet]), [])
            ->assertStatus(200);
        
        $this->assertCount(0, $this->business->fresh()->shifts);

        $this->assertTrue($timesheet->fresh()->isDenied);
    }
}
