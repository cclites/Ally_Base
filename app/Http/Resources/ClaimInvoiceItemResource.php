<?php

namespace App\Http\Resources;

use App\ClaimableExpense;
use App\ClaimableService;
use Illuminate\Http\Resources\Json\Resource;

class ClaimInvoiceItemResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'amount' => $this->resource->amount,
            'amount_due' => $this->resource->amount_due,
            'claim_invoice_id' => $this->resource->claim_invoice_id,
            'related_shift_id' => optional($this->resource->claimable)->shift_id,
            'claimable' => $this->resource->claimable,
            'invoiceable' => $this->resource->invoiceable,
            'date' => $this->resource->date,
//            'claimable_id' => $this->resource->claimable_id,
//            'claimable_type' => $this->resource->claimable_type,
            'type' => $this->getType(),
            'created_at' => $this->resource->created_at,
            'id' => $this->resource->id,
//            'invoiceable_id' => $this->resource->invoiceable_id,
//            'invoiceable_type' => $this->resource->invoiceable_type,
            'rate' => $this->resource->rate,
            'units' => $this->resource->units,
            'updated_at' => $this->resource->updated_at,
            'summary' => $this->resource->invoiceable->getItemName($this->resource->claim->clientInvoice),
//            'summary' => optional($this->invoiceable)->getItemGroup($this->resource->claim->clientInvoice),
        ];
    }

    /**
     * Get display type string of Claimable type.
     *
     * @return string
     */
    public function getType()
    {
        switch ($this->resource->claimable_type) {
            case ClaimableService::class:
                return 'Service';
            case ClaimableExpense::class:
                return 'Expense';
            default:
                return 'ERROR';
        }
    }
}
