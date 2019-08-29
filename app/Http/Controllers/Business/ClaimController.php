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

    public function updateClaimItem( ClaimInvoiceItem $item, Request $request )
    {
        if ( !in_array( $item->claim->business_id, auth()->user()->getBusinessIds() ) ) abort( 403 );

        try {

            \DB::beginTransaction();

                // to update the claim invoice item:
                // calculate the new amount and amount_due
                // create the array with those, rate and units

                $new_rate   = floatval( $request->rate );
                $new_units  = floatval( $request->units );
                $new_amount = $new_rate * $new_units;

                $old_amount    = floatval( $item->amount );
                $amount_change = $old_amount - $new_amount;

                // now that I think about it.. edited items ae pre-transmission.. so the balance would always = the amount and paid would always = 0.. right?
                $new_balance_due = floatval( $item->amount_due ) - $amount_change;

                $item->update([

                    'rate'       => $new_rate,
                    'units'      => $new_units,
                    'amount'     => $new_amount,
                    'amount_due' => $new_balance_due,
                ]);

                $claimable = $item->claimable;

                if( $item->claimable_type == 'App\ClaimableService' ){

                    $claimableValidation = $request->validate([
                        // white list the attributes to update for the claimable relationship

                        'claimable.caregiver_first_name'  => 'required',
                        'claimable.caregiver_last_name'   => 'required',
                        'claimable.caregiver_gender'      => 'nullable',
                        'claimable.caregiver_dob'         => 'nullable',
                        'claimable.caregiver_ssn'         => 'nullable',
                        'claimable.caregiver_medicaid_id' => 'nullable',
                        'claimable.address1'              => 'nullable',
                        'claimable.address2'              => 'nullable',
                        'claimable.city'                  => 'nullable',
                        'claimable.state'                 => 'nullable',
                        'claimable.zip'                   => 'nullable',
                        'claimable.latitude'              => 'nullable',
                        'claimable.longitude'             => 'nullable',
                        'claimable.scheduled_start_time'  => 'required',
                        'claimable.scheduled_end_time'    => 'required',
                        'claimable.visit_start_time'      => 'required',
                        'claimable.visit_end_time'        => 'required',
                        'claimable.evv_start_time'        => 'required',
                        'claimable.evv_end_time'          => 'required',
                        'claimable.checked_in_number'     => 'nullable',
                        'claimable.checked_out_number'    => 'nullable',
                        'claimable.checked_in_latitude'   => 'nullable',
                        'claimable.checked_in_longitude'  => 'nullable',
                        'claimable.checked_out_latitude'  => 'nullable',
                        'claimable.checked_out_longitude' => 'nullable',
                        'claimable.evv_method_in'         => 'nullable',
                        'claimable.evv_method_out'        => 'nullable',
                        'claimable.service_name'          => 'required',
                        'claimable.service_code'          => 'nullable',
                        'claimable.activities'            => 'nullable',
                        'claimable.caregiver_comments'    => 'nullable',
                    ]);

                } else if( $item->claimable_type == 'App\ClaimableExpense' ){

                    $claimableValidation = $request->validate([
                        // white list the attributes to update for the claimable relationship

                        'claimable.name'  => 'required',
                        'claimable.date'  => 'required',
                        'claimable.notes' => 'nullable'
                    ]);
                }

                $claimable->update( $claimableValidation[ 'claimable' ] );

            \DB::commit();
        } catch ( Exception $e ) {

            return $e->getMessage();
        }

        return new SuccessResponse( 'Claim Item has been updated.' );
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
