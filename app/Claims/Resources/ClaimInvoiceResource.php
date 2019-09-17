<?php

namespace App\Claims\Resources;

use App\Claims\ClaimInvoice;
use Illuminate\Http\Resources\Json\Resource;

class ClaimInvoiceResource extends Resource
{
    /**
     * @var ClaimInvoice
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
        if (! $this->resource->relationLoaded('items')) {
            $this->resource->load(['items' => function ($q) {
                $q->orderByRaw('claimable_type desc, date asc');
            }]);
        }

        if (! $this->resource->relationLoaded('client')) {
            $this->resource->load('client');
        }

        if (! $this->resource->relationLoaded('clientInvoice')) {
            $this->resource->load('clientInvoice');
        }

        if (! $this->resource->relationLoaded('payer')) {
            $this->resource->load('payer');
        }

        return [
            'amount' => $this->resource->amount,
            'amount_due' => $this->resource->amount_due,
            'amount_paid' => $this->resource->getAmountPaid(),
            'business_id' => $this->resource->business_id,
            'client' => [
                'name' => $this->resource->client->name,
            ],
            'client_dob' => $this->resource->client_dob,
            'client_first_name' => $this->resource->client_first_name,
            'client_id' => $this->resource->client_id,
            'client_invoice' => [
                'name' => $this->resource->clientInvoice->name,
                'date' => $this->resource->clientInvoice->created_at->toDateTimeString(),
            ],
            'client_invoice_date' => $this->resource->clientInvoice->created_at->toDateTimeString(),
            'client_invoice_id' => $this->resource->client_invoice_id,
            'client_last_name' => $this->resource->client_last_name,
            'client_medicaid_diagnosis_codes' => $this->resource->client_medicaid_diagnosis_codes,
            'client_medicaid_id' => $this->resource->client_medicaid_id,
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
        ];
    }
}
