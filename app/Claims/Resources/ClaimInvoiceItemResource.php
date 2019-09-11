<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;

class ClaimInvoiceItemResource extends Resource
{
    /**
     * @var ClaimInvoiceItem $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'amount' => $this->resource->amount,
            'amount_due' => $this->resource->amount_due,
            'claim_invoice_id' => $this->resource->claim_invoice_id,
            'related_shift_id' => optional($this->resource->claimable)->shift_id,
            'claimable' => $this->mapClaimable(),
            'invoiceable' => $this->resource->invoiceable,
            'date' => $this->resource->date,
            'claimable_id' => $this->resource->claimable_id,
            'claimable_type' => $this->resource->claimable_type,
            'type' => $this->type,
            'id' => $this->resource->id,
            'invoiceable_id' => $this->resource->invoiceable_id,
            'invoiceable_type' => $this->resource->invoiceable_type,
            'rate' => number_format($this->resource->rate, 2),
            'units' => number_format($this->resource->units, 2),
            'summary' => $this->resource->claimable->getName(),
            'start_time' => $this->resource->claimable->getStartTime(),
            'end_time' => $this->resource->claimable->getEndTime(),
        ];
    }

    /**
     * Map the Claimable object resource.
     *
     * @return ClaimableExpenseResource|ClaimableServiceResource
     */
    public function mapClaimable()
    {
        switch ($this->resource->claimable_type) {
            case ClaimableService::class:
                return new ClaimableServiceResource($this->resource->claimable);
            case ClaimableExpense::class:
                return new ClaimableExpenseResource($this->resource->claimable);
            default:
                throw new \InvalidArgumentException('Unknown claimable type.');
        }
    }
}
