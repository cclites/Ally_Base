<?php

namespace Tests\Controller\Business;

use App\Business;
use App\Caregiver;
use App\Client;
use App\OfficeUser;
use App\Shift;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimesheetExportTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();
        $this->disableExceptionHandling();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAnOfficeUserCanExportTimesheets()
    {
        $business = factory(Business::class)->create();
        $client = factory(Client::class)->create(['business_id' => $business->id, 'firstname' => 'John', 'lastname' => 'Doe']);
        $caregiver = factory(Caregiver::class)->create(['firstname' => 'Jane', 'lastname' => 'Doe']);
        $office_user = factory(OfficeUser::class)->create();

        $business->users()->attach($office_user->id);
        $business->assignCaregiver($caregiver);

        $shift = factory(Shift::class)->create([
            'client_id' => $client->id,
            'business_id' => $business->id,
            'caregiver_id' => $caregiver->id,
            'checked_in_time' => Carbon::now()->subDay(),
            'checked_out_time' => Carbon::now()->subDay()->addHours(2),
            'caregiver_comments' => 'Test comments',
        ]);

        $this->actingAs($office_user->user);
        $response = $this->post('/business/reports/print/timesheet-data', [
            'start_date' => Carbon::now()->subDays(2),
            'end_date' => Carbon::now(),
            'client_id' => null,
            'caregiver_id' => null,
            'client_type' => null,
            'export_type' => 'text'
        ]);

        $response->assertSeeText('John Doe');
        $response->assertSeeText('Jane Doe');
        $response->assertSeeText($shift->caregiver_comments);

    }
}
