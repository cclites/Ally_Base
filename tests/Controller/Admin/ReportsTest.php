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
use App\Caregiver;
use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;

class ReportsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     *
     * testing the results of the 'Total Charges Report'
     *
     * Criteria:
     *  - must accept a date range
     *  - must not paginate ( turns out there wasn't any backend code to implement this to begin with )
     *
     * @return void
     */
    public function testing_the_total_charges_report()
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

    /**
     * @test
     * 
     * testing the results of the 'Deposit Invoices Report'
     * 
     * not a very comprehensive test, I just needed to verify that results were indeed coming through and also I needed to understand how this report worked.
     * I had no local data to go off of, so this helped me manually seed my table with tinker.
     * 
     * 
     * Criteria:
     *  - must accept a date range
     */
    public function testing_the_deposit_invoices_report()
    {
        // Step 1: Setup test Admin-User
        $admin = factory( Admin::class )->create();
        $this->actingAs( $admin->user );

        // Step 2: Setup test Business and Caregiver
        $business = factory( Business::class )->create();
        $caregiver = factory( Caregiver::class )->create();

        // Step 3: Create invoices to gather results
        factory( BusinessInvoice::class, 50 )->create([

            'business_id' => $business->id
        ]);
        factory( CaregiverInvoice::class, 50 )->create();

        // Step 4: Verify both sets exist
        $this->assertCount( 50, BusinessInvoice::get() );
        $this->assertDatabaseHas( 'business_invoices', [

            'business_id' => $business->id
        ]);
        // dd( BusinessInvoice::get()->toArray() );

        $this->assertCount( 50, CaregiverInvoice::get() );
        $this->assertDatabaseHas( 'caregiver_invoices', [

            'caregiver_id' => $caregiver->id
        ]);
        // dd( CaregiverInvoice::get()->toArray() );

        // Step 5: Setup a date range to query based on
        $start_date = Carbon::now()->subDay()->format( 'm/d/Y' );
        $end_date   = Carbon::now()->addDay()->format( 'm/d/Y' );

        $query_string = '?json=1&start_date=' . $start_date . '&end_date=' . $end_date;

        // Step 5: Use the official route to grab the data
        $data = $this->get( route( 'admin.invoices.deposits' ) . $query_string )
            ->assertSuccessful();

        // dd( $data );
    }
}
