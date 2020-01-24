<?php
namespace App\Responses\Resources;

use App\Claims\ClaimStatus;
use App\QuickbooksInvoiceStatus;
use Illuminate\Http\Resources\Json\Resource;

class QuickbooksQueueResource extends Resource
{
    /** @var \App\Billing\ClientInvoice */
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
            'name' => $this->resource->name,
            'date' => $this->resource->created_at->toDateTimeString(),
            'client_id' => $this->resource->client->id,
            'client_name' => $this->resource->client->nameLastFirst,
            'payer_id' => optional(optional($this->resource->clientPayer)->payer)->id,
            'payer_name' => optional(optional($this->resource->clientPayer)->payer)->name,
            'total' => $this->resource->getAmount(),
            'balance' => $this->resource->getAmountDue(),
            'quickbooks_invoice_id' => optional($this->resource->quickbooksInvoice)->id,
            'status' => $this->resource->quickbooksInvoice ? $this->resource->quickbooksInvoice->status : QuickbooksInvoiceStatus::READY(),
            'last_status_update' => $this->resource->quickbooksInvoice ? $this->resource->quickbooksInvoice->getLastStatusUpdate()->toDateTimeString() : null,
            'errors' => optional($this->resource->quickbooksInvoice)->errors,
        ];
    }
}
