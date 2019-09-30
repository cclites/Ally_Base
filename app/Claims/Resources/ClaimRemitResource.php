<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimRemitResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimRemit
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
        return [
            'id' => $this->resource->id,
            'amount' => $this->resource->amount,
            'amount_applied' => $this->resource->amount_applied,
            'amount_available' => $this->resource->getAmountAvailable(),
            'business_id' => $this->resource->business->id,
            'business' => [
                'id' => $this->resource->business->id,
                'name' => $this->resource->business->name,
            ],
            'office_location' => $this->resource->business->name,
            'notes' => $this->resource->notes,
            'payer_id' => optional($this->resource->payer)->id,
            'payer_name' => optional($this->resource->payer)->name,
            'payment_type' => $this->resource->payment_type,
            'reference' => $this->resource->reference,
            'date' => $this->resource->date->toDateTimeString(),
            'created_at' => $this->resource->created_at->toDateTimeString(),
            'status' => $this->resource->getStatus(),
            'claim_adjustments_count' => $this->resource->claim_adjustments_count,
        ];
    }
}
