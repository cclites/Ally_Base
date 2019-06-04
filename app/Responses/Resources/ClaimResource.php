<?php
namespace App\Responses\Resources;

use App\Billing\ClaimStatus;

class ClaimResource extends ClientInvoice
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
            'clientPayer' => $this->resource->clientPayer,
            'payer' => optional($this->resource->clientPayer)->payer,
            'payments' => $this->resource->payments,
            'balance' => $this->resource->amount - $this->resource->getAmountPaid(),
            'claim_balance' => empty($this->resource->claim) ? 0.00 : $this->resource->claim->getAmountDue(),
            'claim_status' => empty($this->resource->claim) ? ClaimStatus::NOT_SENT() : $this->resource->claim->status,
            'claim' => $this->resource->claim,
        ]);
    }
}
