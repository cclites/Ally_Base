<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scheduling\ScheduleCreator;
use Carbon\Carbon;
use Tests\TestCase;
use App\Schedule;
use App\CarePlan;

class CarePlansTest extends TestCase
{
    use RefreshDatabase;

    public $client;
    public $caregiver;
    public $business;
    public $officeUser;
    public $activities;

    public function setUp() : void
    {
        parent::setUp();

        $this->client = factory('App\Client')->create();

        $this->business = $this->client->business;

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($this->caregiver);

        // init logged in office user
        $this->officeUser = factory('App\OfficeUser')->create();
        $this->actingAs($this->officeUser->user);
        $this->officeUser->businesses()->attach($this->business->id);

        // create activities to play with
        factory('App\Activity', 5)->create([
            'business_id' => $this->business->id,
        ]);
        $this->activities = $this->business->allActivities()->take(5);
    }

    public function createCarePlan($attributes = [])
    {
        $attributes = array_merge([
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
        ], $attributes);
        return factory('App\CarePlan')->create($attributes);
    }

    public function createSchedule($date, $carePlan)
    {
        $creator = app()->make(ScheduleCreator::class);

        $creator->startsAt($date)
            ->duration(300)
            ->assignments($this->business->id, $this->client->id, $this->caregiver->id)
            ->attachCarePlan($carePlan->id);

        $schedules = $creator->create();

        return Schedule::find($schedules[0]['id']);
    }

    public function getRoute($action = 'index', $carePlan = null)
    {
        $params = ['client' => $this->client];
        if (!empty($carePlan)) {
            $params['care_plan'] = $carePlan;
        }
        return route("business.care-plans.$action", $params);
    }

    /** @test */
    public function a_clients_care_plans_should_be_listed_with_future_schedules_count()
    {
        $plan1 = $this->createCarePlan();
        $this->createSchedule(Carbon::parse('next monday'), $plan1);

        $plan2 = $this->createCarePlan();
        $this->createSchedule(Carbon::parse('yesterday'), $plan2);
        $this->createSchedule(Carbon::parse('today'), $plan2);
        $this->createSchedule(Carbon::parse('today +7 days'), $plan2);
        $this->createSchedule(Carbon::parse('today +8 days'), $plan2);
        
        $this->json('get', $this->getRoute())
            ->assertJsonFragment([
                'name' => $plan1->name,
                'future_schedules_count' => "1",
            ])
            ->assertJsonFragment([
                'name' => $plan2->name,
                'future_schedules_count' => "2",
            ]);
    }

    /** @test */
    public function a_care_plan_can_be_deleted()
    {
        $plan = $this->createCarePlan();

        $this->assertCount(1, $this->client->fresh()->carePlans);
        
        $this->json('delete', $this->getRoute('destroy', $plan))
            ->assertStatus(200);
        
        $this->assertCount(0, $this->client->fresh()->carePlans);
    }

    /** @test */
    public function if_a_care_plan_is_deleted_it_should_be_removed_only_from_future_schedules()
    {
        $plan = $this->createCarePlan();
        $schedule = $this->createSchedule(Carbon::parse('today +7 days'), $plan);
        $schedule2 = $this->createSchedule(Carbon::parse('today +8 days'), $plan);
        $schedule3 = $this->createSchedule(Carbon::parse('yesterday'), $plan);

        $this->json('delete', $this->getRoute('destroy', $plan))
            ->assertStatus(200);
        
        $this->assertNull($schedule->fresh()->care_plan_id);
        $this->assertNull($schedule2->fresh()->care_plan_id);
        $this->assertNotNull($schedule3->fresh()->care_plan_id);
    }

    /** @test */
    public function a_care_plan_can_have_a_name_and_multiple_activities()
    {
        $this->json('post', $this->getRoute('store'), [
            'name' => 'Test Plan',
            'activities' => $this->activities->pluck('id'),
        ])->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Plan']);

        $this->assertCount(5, CarePlan::first()->activities);
    }

    /** @test */
    public function a_care_plan_requires_a_name_and_one_activity()
    {
        $this->json('post', $this->getRoute('store'), [])
            ->assertJsonValidationErrors(['name', 'activities']);
    }

    /** @test */
    public function a_care_plan_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $plan = $this->createCarePlan();

        $data = [
            'name' => 'New Name', 
            'notes' => 'New Notes', 
            'activities' => [$this->activities->first()->id]
        ];

        $this->json('patch', $this->getRoute('update', $plan), $data)
            ->assertStatus(200)
            ->assertJsonFragment(['name' => $data['name']])
            ->assertJsonFragment(['notes' => $data['notes']]);

        $this->assertCount(1, $plan->fresh()->activities);
    }
}