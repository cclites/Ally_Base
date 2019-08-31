<?php

namespace App\Http\Controllers\Business;

use App\Claims\ClaimInvoiceItem;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClaimInvoiceItemController extends BaseController
{
    /**
     * Update a ClaimInvoiceItem.
     *
     * @param ClaimInvoiceItem $item
     * @param Request $request
     * @return SuccessResponse|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ClaimInvoiceItem $item, Request $request)
    {
        $this->authorize('update', $item->claim);

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

                // update the claim_invoice_item
                $item->update([

                    'rate'       => $new_rate,
                    'units'      => $new_units,
                    'amount'     => $new_amount,
                    'amount_due' => $new_balance_due,
                ]);

                // update the claim_invoice
                $claim_invoice = $item->claim;
                $claim_invoice->amount     = floatval( $claim_invoice->amount ) - $amount_change;
                $claim_invoice->amount_due = floatval( $claim_invoice->amount_due ) - $amount_change;
                $claim_invoice->update();

                // update the claimable reference
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

                    if( !empty( $claimableValidation[ 'claimable' ][ 'caregiver_dob'        ] ) ) $claimableValidation[ 'claimable' ][ 'caregiver_dob' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'caregiver_dob' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'scheduled_start_time' ] ) ) $claimableValidation[ 'claimable' ][ 'scheduled_start_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'scheduled_start_time' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'scheduled_end_time'   ] ) ) $claimableValidation[ 'claimable' ][ 'scheduled_end_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'scheduled_end_time' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'visit_start_time'     ] ) ) $claimableValidation[ 'claimable' ][ 'visit_start_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'visit_start_time' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'visit_end_time'       ] ) ) $claimableValidation[ 'claimable' ][ 'visit_end_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'visit_end_time' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'evv_start_time'       ] ) ) $claimableValidation[ 'claimable' ][ 'evv_start_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'evv_start_time' ] );
                    if( !empty( $claimableValidation[ 'claimable' ][ 'evv_end_time'         ] ) ) $claimableValidation[ 'claimable' ][ 'evv_end_time' ] = Carbon::parse( $claimableValidation[ 'claimable' ][ 'evv_end_time' ] );

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
        } catch (\Exception $e) {
            \DB::rollBack();
            return new ErrorResponse(500, "Error updating this item: " . $e->getMessage());
        }

        return new SuccessResponse( 'Claim Item has been updated.');
    }

    /**
     * Remove a ClaimInvoiceItem.
     *
     * @param ClaimInvoiceItem $item
     * @return SuccessResponse|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(ClaimInvoiceItem $item)
    {
        $this->authorize('update', $item->claim);

        try {
            \DB::beginTransaction();
                $claim = $item->claim;
                $claim->amount -= $item->amount;
                $claim->amount_due -= $item->amount_due;
                $claim->update();

                $item->claimable->delete();
                $item->delete();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return new ErrorResponse(500, "Error deleting this item: " . $e->getMessage());
        }

        return new SuccessResponse('Claim Item has been deleted.');
    }
}
