<?php

namespace Tests\Controller\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Admin;
use App\Business;
use App\Billing\Payment;
use App\Billing\ClientPayer;
use Carbon\Carbon;

class TestReports extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     * 
     * testing the results of the 'Total Charges Report'
     * 
     * Criteria:
     *  - must accept a date range
     *  - must not paginate
     *
     * @return void
     */
    public function testExample()
    {
        // Step 1: Setup test Admin-User
        $admin = factory( Admin::class )->create();
        $this->actingAs( $admin->user );

        // Step 2: Setup test Business and Payer
        $business = factory( Business::class )->create();
        $clientpayer = factory( ClientPayer::class )->create();

        // Step 3: Setup a number of test Payments
        factory( Payment::class, 50 )->create([

            'client_id'        => $clientpayer->client_id,
            'business_id'      => $business->id
        ]);

        // Step 4: Verify Payments Exist
        $this->assertCount( 50, Payment::get() );
        $this->assertDatabaseHas( 'payments', [

            'client_id' => $clientpayer->client_id
        ]);
        // dd( Payment::get()->toArray() );

        // Step 4: Setup a date range for selecting items
        $start_date = Carbon::now()->subDay()->format( 'm/d/Y' );
        $end_date   = Carbon::now()->addDay()->format( 'm/d/Y' );

        $query_string = '?json=1&startdate=' . $start_date . '&enddate=' . $end_date;

        // Step 5: Use the official route to grab the data
        $this->get( route( 'admin.reports.total_charges_report' ) . $query_string )
            ->assertSuccessful()
            ->assertJsonStructure([ 'data', 'totals' ]);

        // im not sure what other tests are needed to run to verify anything, but this function is well enough to have a CLI way to verify that the proper results are coming back
    }
}
