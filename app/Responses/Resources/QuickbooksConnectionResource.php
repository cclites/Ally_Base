<?php
namespace App\Responses\Resources;

use App\QuickbooksConnection;

class QuickbooksConnectionResource extends ClientInvoice
{
    /** @var QuickbooksConnection */
    public $resource;

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
            'is_desktop' => $this->resource->is_desktop,
            'desktop_api_key' => $this->resource->desktop_api_key,
            'last_connected_at' => optional($this->resource->last_connected_at)->toDateTimeString(),
        ]);
    }
}
