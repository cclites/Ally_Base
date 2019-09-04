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
            'claimable' => $this->mapClaimable(),
            'invoiceable' => $this->resource->invoiceable,
            'date' => $this->resource->date,
            'claimable_id' => $this->resource->claimable_id,
            'claimable_type' => $this->resource->claimable_type,
            'type' => $this->getType(),
            'id' => $this->resource->id,
            'invoiceable_id' => $this->resource->invoiceable_id,
            'invoiceable_type' => $this->resource->invoiceable_type,
            'rate' => number_format($this->resource->rate, 2),
            'units' => number_format($this->resource->units, 2),
            'summary' => $this->resource->claimable->getName(),
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
