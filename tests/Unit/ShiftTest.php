<?php

namespace Tests\Unit;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use App\ShiftActivity;
use App\ShiftIssue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    public $caregiver;
    public $business;
    public $client;
    public $shift;

    public function setUp()
    {
        parent::setUp();
        $this->client = factory(Client::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();
        $this->business = factory(Business::class)->create();
        $this->shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'business_id' => $this->business->id,
        ]);
    }

    public function testShiftCanBeCreated()
    {
        return $this->assertTrue(true);
    }

    public function testShiftDuration()
    {
        $duration = 150;
        $start = (new \DateTime())->sub(new \DateInterval('P1D'));
        $end = (new \DateTime())->sub(new \DateInterval('P1DT' . $duration . 'M'));

        $shift = factory(Shift::class)->make([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'business_id' => $this->business->id,
            'checked_in_time' => $start->format('Y-m-d H:i:s'),
            'checked_out_time' => $end->format('Y-m-d H:i:s'),
        ]);

        $this->assertEquals(2.5, $shift->duration());
    }

    public function testShiftHasClient()
    {
        $this->assertInstanceOf(Client::class, $this->shift->client);
    }

    public function testShiftHasBusiness()
    {
        $this->assertInstanceOf(Business::class, $this->shift->business);
    }

    public function testShiftHasCaregiver()
    {
        $this->assertInstanceOf(Caregiver::class, $this->shift->caregiver);
    }

    public function testShiftCanHaveActivities()
    {
        $activity1 = factory(Activity::class)->create(['business_id' => $this->business->id]);
        $activity2 = factory(Activity::class)->create(['business_id' => $this->business->id]);

        $this->shift->activities()->attach($activity1);
        $this->shift->activities()->attach($activity2);

        $this->assertCount(2, $this->shift->activities);
    }

    public function testCaregiverCanAddOtherActivities()
    {
        $custom = new ShiftActivity([
            'shift_id' => $this->shift->id,
            'other' => 'Hello World',
            'completed' => true
        ]);

        $this->shift->otherActivities()->save($custom);

        $this->assertCount(1, $this->shift->otherActivities);
        $this->assertInstanceOf(ShiftActivity::class, $this->shift->otherActivities->first());
    }

    public function testShiftCanHaveIssues()
    {
        $issue = factory(ShiftIssue::class)->make();
        $this->shift->issues()->save($issue);

        $this->assertCount(1, $this->shift->issues);
        $this->assertInstanceOf(ShiftIssue::class, $this->shift->issues->first());
    }
}