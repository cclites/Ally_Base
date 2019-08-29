<?php

namespace Tests\Feature;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Invoiceable\ShiftExpense;
use App\Billing\Service;
use App\ClaimableExpense;
use App\ClaimableService;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimInvoiceItem;
use App\Responses\Resources\ClaimResource;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClaimsARTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp()
    {
        parent::setUp();

        $this->business = factory('App\Business')->create();
        $this->chain = $this->business->chain;
        
        $this->client = factory('App\Client')->create(['business_id' => $this->business->id]);

        $this->caregiver = factory('App\Caregiver')->create();
        $this->business->assignCaregiver($this->caregiver);
        
        $this->officeUser = factory('App\OfficeUser')->create(['chain_id' => $this->chain->id]);
        $this->business->users()->attach($this->officeUser);

        $this->payer = factory('App\Billing\Payer')->create(['chain_id' => $this->chain->id]);
        $this->actingAs($this->officeUser->user);
    }

    /**
     * @test
     * 
     * overall CR-D test for claim_invoices form client_invoices
     * 
     * - Reading the table, helps make correct adjustments to the return value structure and ensure certain elements exist
     * - Creating a claim from a client_invoice
     * - Deleting the entire claim
     * 
     * this test is not testing updating the claim_invoice at this moment
     * 
     * on a side note, I personally dont mind large tests like this that cover many things because it does so in an incremental fashion and errors usually tell you the line where somethign went wrong anyways,
     * however I know that this is not everyone's flavor so let me know if its desired to break this up.
     */
    public function office_user_can_create_and_delete_claims()
    {

        // Step 1: World building, create our client_invoice
        // given that we have a client invoice..
        $client_invoice = factory( ClientInvoice::class )->create();
        $this->assertDatabaseHas( 'client_invoices', $client_invoice->toArray() );

        // ..give our invoice an associated shift/service
        $shift = factory( Shift::class )->create();
        $service = factory( Service::class )->create();
        $shift->service()->associate( $service );
        $shift->save();

        // save these items..
        $client_invoice->items()->save( factory( ClientInvoiceItem::class )->make([

            'invoice_id'       => $client_invoice->id,
            'invoiceable_type' => 'shifts',
            'invoiceable_id'   => $shift->id
        ]));
        $this->assertEquals( 1, $client_invoice->items->count() );

        $item = ClientInvoiceItem::first();
        $this->assertEquals( 1, $item->count() );

        // ..and an expense
        $expense = factory( ShiftExpense::class )->create([ 'shift_id' => $shift->id ]);
        $client_invoice->items()->save( factory( ClientInvoiceItem::class )->make([

            'invoice_id'       => $client_invoice->id,
            'invoiceable_type' => 'shift_expenses',
            'invoiceable_id'   => $expense->id
        ]));

        $item = ClientInvoiceItem::get();
        $this->assertEquals( 2, $client_invoice->refresh()->items->count() );

        // Step 2: Create the claim invoice
        // we can create a claim-invoice based off of it using our route..
        $this->post( route( 'business.claims.store' ), [ 'client_invoice_id' => $client_invoice->id ] );

        // and prove it exists
        $claim = ClaimInvoice::where( 'client_invoice_id', $client_invoice->id )->first();
        $this->assertEquals( 1, $claim->count() );

        // prove the items exist in the copied claim
        $this->assertDatabaseHas( 'claim_invoice_items', [

            'claim_invoice_id' => $claim->id,
            'claimable_type'   => 'App\ClaimableService'
        ]);
        $this->assertDatabaseHas( 'claim_invoice_items', [

            'claim_invoice_id' => $claim->id,
            'claimable_type'   => 'App\ClaimableExpense',
        ]);
        // quick dump to verify manually
        // dd( $claim->items );

        // Step 3: Fetch and read the claim_invoice data
        $this->get( route( 'business.claims-queue', [

            'json'        => 1,
            'businesses'  => null,
            'start_date'  => null,
            'end_date'    => null,
            'invoiceType' => null,
            'client_id'   => null,
            'payer_id'    => null,
        ]))->assertJsonFragment( $claim->toArray() );

        // Step 4: Delete the entire claim_invoice
        // we can also delete the claim invoice using our route function..
        $data = $this->delete( route( 'business.claims.destroy', [ 'claim' => $claim->id ] ) );

        // prove that everything is in fact deleted..
        $this->assertDatabaseMissing( 'claim_invoices', $claim->toArray() );
        $this->assertCount( 0, ClaimInvoiceItem::get() );
        $this->assertCount( 0, ClaimableService::get() );
        $this->assertCount( 0, ClaimableExpense::get() );
    }

    /**
     * @test
     * 
     * This test takes a claim and asserts that CRUD operations can be done on that independent of the original invoice
     */
    public function office_user_can_manage_claim_specific_data()
    {
        $this->withoutExceptionHandling();

        // Step 1: World building, create our client_invoice
        // given that we have a client invoice..
        $client_invoice = factory( ClientInvoice::class )->create();
        $this->assertDatabaseHas( 'client_invoices', $client_invoice->toArray() );

        // ..give our invoice an associated shift/service
        $shift = factory( Shift::class )->create();
        $service = factory( Service::class )->create();
        $shift->service()->associate( $service );
        $shift->save();

        // save these items..
        $client_invoice->items()->saveMany( factory( ClientInvoiceItem::class, 3 )->make([

            'invoice_id'       => $client_invoice->id,
            'invoiceable_type' => 'shifts',
            'invoiceable_id'   => $shift->id
        ]));
        $this->assertEquals( 3, $client_invoice->items->count() );

        $item = ClientInvoiceItem::first();
        $this->assertEquals( 3, $item->count() );

        // ..and an expense
        $expense = factory( ShiftExpense::class )->create([ 'shift_id' => $shift->id ]);
        $client_invoice->items()->saveMany( factory( ClientInvoiceItem::class, 3 )->make([

            'invoice_id'       => $client_invoice->id,
            'invoiceable_type' => 'shift_expenses',
            'invoiceable_id'   => $expense->id
        ]));

        $this->assertEquals( 6, $client_invoice->refresh()->items->count() );

        // Step 2: Create the claim invoice
        // we can create a claim-invoice based off of it using our route..
        $this->post( route( 'business.claims.store' ), [ 'client_invoice_id' => $client_invoice->id ] );

        // and prove it exists
        $claim = ClaimInvoice::where( 'client_invoice_id', $client_invoice->id )->first();
        $this->assertEquals( 1, $claim->count() );

        // Step 3: Grab a random item to try deleting
        $item = ClaimInvoiceItem::inRandomOrder()->with( 'claimable' )->first();

        $this->delete( route( 'business.claims.item.delete', [ 'item' => $item->id ] ) );

        $this->assertEquals( 5, ClaimInvoiceItem::count() );
        $this->assertNotEquals( floatval( $claim->amount ), floatval( $claim->refresh()->amount ) ); // after deleting, the amount should be adjusted

        // Step 4: Grab a random item and try editing it
        $items = ClaimInvoiceItem::with( 'claimable' )->where( 'claimable_type', 'App\ClaimableService' )->get();

        $editing_item          = $items->first();
        $stolen_data_model     = $items->last();
        $stolen_data_model->id = $editing_item->id;

        $this->patch( route( 'business.claims.item.update', [ 'item' => $editing_item->id ] ), $stolen_data_model->toArray() );

        // test a few specific items to make sure that they are equal
        $this->assertEquals( $editing_item->refresh()->rate, $stolen_data_model->rate );
        $this->assertEquals( $editing_item->refresh()->units, $stolen_data_model->units );
        $this->assertEquals( $editing_item->refresh()->claimable->caregiver_first_name, $stolen_data_model->claimable->caregiver_first_name );
        $this->assertEquals( $editing_item->refresh()->claimable->caregiver_last_name, $stolen_data_model->claimable->caregiver_last_name );
        $this->assertEquals( $editing_item->refresh()->claimable->service_name, $stolen_data_model->claimable->service_name );

        $this->get( '/business/claims/' . $claim->id . '/edit' );
    }
}
