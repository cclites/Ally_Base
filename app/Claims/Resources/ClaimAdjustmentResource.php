<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimAdjustmentResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimAdjustment
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->id,
            'claim_remit_id' => $this->resource->claim_remit_id,

            'claim_invoice_id' => $this->resource->claim_invoice_id,
            'claim_invoice_date' => null,
            'claim_invoice_name' => null,

            'claim_invoice_item_id' => $this->resource->claim_invoice_item_id,
            'item' => 'Interest',
            'item_total' => null,

            'client_id' => null,
            'client_name' => null,

            'amount_applied' => $this->resource->amount_applied,
            'adjustment_type' => $this->resource->adjustment_type,
            'is_interest' => $this->resource->is_interest,
            'note' => $this->resource->note,
            'created_at' => $this->resource->created_at->toDateTimeString(),
        ];

        if (! $this->resource->is_interest) {
            $data = array_merge($data, [
                'claim_invoice_date' => $this->resource->claimInvoice->getDate()->toDateTimeString(),
                'claim_invoice_name' => $this->resource->claimInvoice->getName(),
                'item' => $this->resource->claimInvoiceItem->getItemSummary(),
                'item_total' => $this->resource->claimInvoiceItem->amount,
                'client_id' => $this->resource->claimInvoice->client->id,
                'client_name' => $this->resource->claimInvoice->client->name,
            ]);
        }

        return $data;
    }
}
