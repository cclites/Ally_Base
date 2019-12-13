<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Billing\ClientInvoice;

class ManageClaimsResource extends Resource
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
        $invoiceAmount = floatval($this->resource->clientInvoices->sum('amount'));

        $client = null;
        $totalClients = $this->resource->clientInvoices->unique('client_id')->values()->count();
        if ($totalClients == 1) {
            $client = $this->resource->clientInvoices[0]->client;
        }

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'created_at' => optional($this->resource->created_at)->toDateTimeString(),
            'type' => $this->resource->claim_invoice_type,
            'client_id' => $client ? $client->id : null,
            'client_name' => $client ? $client->name_last_first : null,
            'payer_id' => $this->resource->payer_id,
            'payer_name' => $this->resource->payer_name,
            'amount' => $this->resource->getAmount(),
            'paid' => $this->resource->getAmountPaid(),
            'balance' => $this->resource->getAmountDue(),
            'status' => $this->resource->status,
            'transmission_method' => $this->resource->getTransmissionMethod(),
            'modified_at' => optional($this->resource->modified_at)->toDateTimeString(),

            'invoice_id' => $this->resource->clientInvoices->count() > 1 ? '-' : $this->resource->clientInvoices[0]->id,
            'invoice_name' => $this->resource->clientInvoices->count() > 1 ? '-' : $this->resource->clientInvoices[0]->name,
            'invoice_amount' => $invoiceAmount,
            'amount_mismatch' => $this->resource->hasAmountMismatch(),
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
