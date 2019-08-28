<?php

namespace App\Http\Controllers\Business;

use App\Billing\ClientInvoice;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimInvoiceFactory;
use App\Claims\ClaimInvoiceItem;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\ErrorResponse;
use Exception;

class ClaimController extends Controller
{
    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Exception
     */
    public function store( Request $request, ClaimInvoiceFactory $factory )
    {
        $clientInvoice = ClientInvoice::findOrFail( $request->client_invoice_id );

        $claim = $factory->createFromClientInvoice( $clientInvoice );

        return new SuccessResponse( 'Claim has been created.', compact( 'claim' ) );
    }

    /**
     * 
     * Show a claim_invoice
     * 
     * @param ClaimInvoice $claim
     * @param string $view
     */
    public function show( ClaimInvoice $claim, string $view = InvoiceViewFactory::HTML_VIEW )
    {
        if ( !in_array( $claim->business_id, auth()->user()->getBusinessIds() ) ) abort( 403 );

        $strategy = InvoiceViewFactory::create( $claim, $view );

        $viewGenerator = new InvoiceViewGenerator( $strategy );

        return $viewGenerator->generateNewClaimInvoice( $claim );
    }

    /**
     * grab the data for a specific claim_invoice to populate the edit-modal
     */
    public function edit( ClaimInvoice $claim )
    {
        if ( !in_array( $claim->business_id, auth()->user()->getBusinessIds() ) ) abort( 403 );

        $claim->load([ 'items', 'client' ]);

        return response()->json( $claim );
    }

    public function update( ClaimInvoice $claim, Request $request )
    {

        // validate data
        $validated = $request->validate([

            'client_first_name'               => 'sometimes|required',
            'client_last_name'                => 'sometimes|required',
            'client_medicaid_diagnosis_codes' => 'nullable',
            'client_medicaid_id'              => 'nullable',
            'payer_code'                      => 'nullable',
            'payer_name'                      => 'sometimes|required',
            'plan_code'                       => 'nullable',
            'transmission_method'             => 'nullable'
        ]);

        // make the update
        $claim->update( $validated );

        // return
        return response()->json( $claim->refresh() );
    }

    public function deleteClaimItem( ClaimInvoiceItem $item )
    {
        if ( !in_array( $item->claim->business_id, auth()->user()->getBusinessIds() ) ) abort( 403 );

        try {

            \DB::beginTransaction();

                $claim = $item->claim;

                $claim->amount -= $item->amount;
                $claim->amount_due -= $item->amount_due;

                $claim->update();

                $item->claimable->delete();
                $item->delete();
            \DB::commit();
        } catch ( Exception $e ) {

            return $e->getMessage();
        }

        return new SuccessResponse( 'Claim Item has been deleted.' );
    }

    /**
     * Create a ClaimInvoice.
     *
     * @param Request $request
     * @param ClaimInvoiceFactory $factory
     * @return SuccessResponse
     * @throws \Exception
     */
    public function destroy( Request $request, ClaimInvoiceFactory $factory )
    {
        $claim = ClaimInvoice::findOrFail( $request->claim );
        if ( !in_array( $claim->business_id, auth()->user()->getBusinessIds() ) ) abort( 403 );

        $factory->hardDeleteClaimInvoice( $claim );

        return new SuccessResponse( 'Claim has been deleted.' );
    }
}
