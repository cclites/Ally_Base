<?php

namespace Tests\Controller\Business;

use App\Activity;
use App\Business;
use App\Caregiver;
use App\Client;
use App\OfficeUser;
use App\Shift;
use App\ShiftActivity;
use App\ShiftIssue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Check that an office user can print a shift's details.
     *
     * @return void
     */
    public function testAnOfficeUserCanPrintShiftDetails()
    {
        $office_user = factory(OfficeUser::class)->create();
        $business = factory(Business::class)->create();
        $client = factory(Client::class)->create(['business_id' => $business->id]);
        $caregiver = factory(Caregiver::class)->create();

        // attach users to the business
        $business->caregivers()->attach($caregiver->id);
        $business->users()->attach($office_user->id);

        $business = Business::with('clients', 'caregivers')->find($business->id);

        $shift = factory(Shift::class)->create([
            'client_id' => $client->id,
            'business_id' => $business->id,
            'caregiver_id' => $caregiver->id
        ]);

        $issue = factory(ShiftIssue::class)->create(['shift_id' => $shift->id]);
        $activity = factory(Activity::class)->create(['business_id' => $business->id]);
        $shift->activities()->attach($activity->id);

        $this->actingAs($office_user->user);

        $response = $this->get("/business/shifts/{$shift->id}/print");
        $response->assertSeeText($client->firstname);
        $response->assertSeeText($client->lastname);
        $response->assertSeeText($caregiver->firstname);
        $response->assertSeeText($caregiver->lastname);
        $response->assertSeeText($issue->comments);
        $response->assertSeeText($activity->name);
    }
}
