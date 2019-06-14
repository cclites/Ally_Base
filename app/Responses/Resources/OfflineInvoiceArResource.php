<?php
namespace App\Responses\Resources;

use App\Billing\ClaimStatus;

class OfflineInvoiceArResource extends ClientInvoice
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'client' => $this->resource->client,
            'payments' => $this->resource->payments,
            'balance' => (float) bcsub($this->resource->amount, $this->resource->getAmountPaid(), 2),
        ]);
    }
}
