<?php
namespace App\Responses\Resources;

use App\Billing\ClaimStatus;
use Carbon\Carbon;

class ClaimResource extends ClientInvoice
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request )
    {
        // swapped out the old 'claim' for 'claimInvoice'
        $claim = $this->resource->claimInvoice;

        return array_merge( parent::toArray( $request ), [

            // client-related data
            'client'        => $this->resource->client,
            'clientPayer'   => $this->resource->clientPayer,
            'payer'         => optional( $this->resource->clientPayer )->payer,
            'payments'      => $this->resource->payments,

            // these are necessary for the new claims-queue because when the registry updates the client name on the claim it only updates that claim record not the actual client
            'client_name'   => empty( $claim ) ? $this->resource->client->name : ucwords( implode( ' ', [ $claim->client_first_name, $claim->client_last_name ] ) ),

            'balance'       => $this->resource->amount - $this->resource->getAmountPaid(),

            // claim-related data
            'claim'         => $claim,
            'claim_total'   => empty( $claim ) ? 0.00 : $claim->getAmount(),
            'claim_paid'    => empty( $claim ) ? 0.00 : $claim->getAmountPaid(),
            'claim_balance' => empty( $claim ) ? 0.00 : $claim->getAmountDue(),
            'claim_status'  => empty( $claim ) ? ClaimStatus::NOT_SENT() : $claim->status,
            'claim_date'    => empty( $claim ) ? null : Carbon::parse( $claim->created_at )->format( 'm/d/Y h:i A' ),

            // There isnt a 'service' on the new claim..
            'claim_service' => optional( $claim )->service
        ]);
    }
}
