<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Carbon\Carbon;

class ClaimCreatorResource extends Resource
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

        return [
            'selected' => false,
            'id' => $this->resource->id,
            'created_at' => $this->resource->created_at->toDateTimeString(),
            'amount' => $this->resource->amount,
            'name' => $this->resource->name,
            'client_id' => $this->resource->client->id,
            'client_name' => $this->resource->client->name_last_first,
            'payer_id' => $this->resource->clientPayer ? $this->resource->clientPayer->payer->id : '-',
            'payer_name' => $this->resource->clientPayer ? $this->resource->clientPayer->payer->name : '-',
            'is_paid' => $this->resource->is_paid ? 'Yes' : 'No',
            'claim_id' => empty($claim) ? '-' : $claim->id,
            'claim_name' => empty($claim) ? '-' : $claim->getName(),
            'claim_date' => empty($claim) ? null : Carbon::parse($claim->created_at)->format('m/d/Y h:i A'),
        ];
    }
}
