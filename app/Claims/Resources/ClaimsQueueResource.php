<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Carbon\Carbon;

class ClaimsQueueResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\ClientInvoice
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
        /** @var \App\Claims\ClaimInvoice $claim */
        $claim = $this->resource->claimInvoice;

        return array_merge(parent::toArray($request), [
            'client' => $this->resource->client,
            'clientPayer' => $this->resource->clientPayer,
            'payer' => optional($this->resource->clientPayer)->payer,
            'payments' => $this->resource->payments,
            'client_name' => empty($claim) ? $this->resource->client->name : ucwords(implode(' ', [$claim->client_first_name, $claim->client_last_name])),
            'balance' => $this->resource->amount - $this->resource->getAmountPaid(),
            'claim' => new ClaimInvoiceResource($claim),
            'claim_total' => empty($claim) ? null : $claim->getAmount(),
            'claim_paid' => empty($claim) ? null : $claim->getAmountPaid(),
            'claim_balance' => empty($claim) ? null : $claim->getAmountDue(),
            'claim_status' => empty($claim) ? null : $claim->status,
            'claim_date' => empty($claim) ? null : Carbon::parse($claim->created_at)->format('m/d/Y h:i A'),
            'claim_service' => optional($claim)->service,
            'amount_mismatch' => empty($claim) ? null : $claim->hasAmountMismatch(),
        ]);
    }
}
