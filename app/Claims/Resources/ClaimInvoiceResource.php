<?php

namespace App\Claims\Resources;

use App\Billing\ClientInvoice;
use Illuminate\Http\Resources\Json\Resource;

class ClaimInvoiceResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimInvoice
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
        if (!$this->resource->relationLoaded('items')) {
            $this->resource->load(['items' => function ($q) {
                $q->orderByRaw('claimable_type desc, date asc');
            }]);
        }

        if (!$this->resource->relationLoaded('client')) {
            $this->resource->load('client');
        }

        if (!$this->resource->relationLoaded('clientInvoices')) {
            $this->resource->load('clientInvoices');
        }

        if (!$this->resource->relationLoaded('payer')) {
            $this->resource->load('payer');
        }

        $client = $this->resource->getSingleClient();

        return [
            'amount' => $this->resource->amount,
            'amount_due' => $this->resource->amount_due,
            'amount_paid' => $this->resource->getAmountPaid(),
            'business_id' => $this->resource->business_id,
            'type' => $this->resource->claim_invoice_type,

            'created_at' => $this->resource->created_at,
            'id' => $this->resource->id,
            'items' => ClaimInvoiceItemResource::collection($this->items),
            'name' => $this->resource->name,
            'payer' => $this->resource->payer ? [
                'name' => $this->resource->payer->name,
            ] : null,
            'payer_code' => $this->resource->payer_code,
            'payer_id' => $this->resource->payer_id,
            'payer_name' => $this->resource->payer_name,
            'plan_code' => $this->resource->plan_code,
            'status' => $this->resource->status,
            'transmission_method' => $this->resource->transmission_method,
            'modified_at' => $this->resource->modified_at,
            'has_expenses' => $this->resource->getHasExpenses(),

//            'client' => [
//                'name' => $this->resource->client->nameLastFirst,
//            ],
            'client_id' => $this->resource->client_id,
            'client_name' => $client ? $client->name : '',
//            'client_invoice' => [
//                'name' => $this->resource->clientInvoice->name,
//                'date' => $this->resource->clientInvoice->created_at->toDateTimeString(),
//            ],
            'client_invoice_id' => $this->resource->hasMultipleInvoices() ? '' : optional($this->resource->clientInvoices[0])->id,
            'client_invoice_name' => $this->resource->hasMultipleInvoices() ? '' : optional($this->resource->clientInvoices[0])->name,
            'client_invoice_date' => $this->resource->hasMultipleInvoices() ? '' : optional($this->resource->clientInvoices[0]->created_at)->toDateTimeString(),
            'client_invoice_amount' => $this->resource->getTotalInvoicedAmount(),
            'invoices' => $this->resource->clientInvoices->map(function (ClientInvoice $invoice) {
                return [
                    'id' => $invoice->id,
                    'name' => $invoice->getName(),
                    'amount' => $invoice->getAmount(),
                    'client_id' => $invoice->client_id,
                    'client_name' => $invoice->client->name_last_first,
                    'created_at' => optional($invoice->created_at)->toDateTimeString(),
                ];
            })
        ];
    }
}
