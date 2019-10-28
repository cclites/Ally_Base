<?php
namespace App\Responses\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class DepositInvoice extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\Contracts\DepositInvoiceInterface
     */
    public $resource;

    /**
     * @param \Illuminate\Support\Collection $items
     * @return \Illuminate\Support\Collection
     */
    public function groupItems(Collection $items)
    {
        return $items->sort('date')->groupBy('group');
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'invoice_type' => $this->resource instanceof Model ? maps_from_model($this->resource) : null,
            'notes' => $this->resource->notes ?? '',
            'invoice_id' => $this->resource->id,
            'name' => $this->resource->getName(),
            'date' => $this->resource->getDate(),
            'amount' => $this->resource->getAmount(),
            'amount_paid' => $this->resource->getAmountPaid(),
            'amount_due' => $this->resource->getAmountDue(),
            'created_at' => optional($this->resource->created_at)->toDateTimeString(),
            'recipient' => $this->resource->getRecipient()->name(),
            'caregiver' => $this->whenLoaded('caregiver'),
            'caregiver_on_hold' => $this->whenLoaded('caregiver', function() {
                return $this->resource->caregiver->isOnHold();
            }),
            'payment_hold_notes' => $this->whenLoaded('caregiver', function() {
                return optional( $this->resource->caregiver->paymentHold )->notes;
            }),
            'business' => $this->whenLoaded('business'),
            'business_on_hold' => $this->whenLoaded('business', function() {
                return $this->resource->business->isOnHold();
            }),
            'items' => $this->whenLoaded('items', function() {
                return $this->groupItems($this->resource->getItems())->toArray();
            }),
            'deposits' => $this->whenLoaded('deposits'),
            'no_bank_account' => optional($this->resource->getRecipient())->bankAccount === null, // TODO: Clean this up
        ];
    }
}
