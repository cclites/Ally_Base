<?php
namespace App\Responses\Resources;

class QuickbooksConnectionResource extends ClientInvoice
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
            'business_id' => $this->resource->business_id,
            'company_name' => $this->resource->company_name,
            'shift_service' => $this->resource->shift_service,
            'mileage_service' => $this->resource->mileage_service,
            'adjustment_service' => $this->resource->adjustment_service,
            'refund_service' => $this->resource->refund_service,
            'expense_service' => $this->resource->expense_service,
            'is_authenticated' => $this->resource->isAuthenticated(),
            'allow_shift_overrides' => $this->resource->allow_shift_overrides,
            'created_at' => $this->resource->created_at->toDateTimeString(),
        ]);
    }
}
