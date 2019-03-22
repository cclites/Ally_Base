<?php
namespace App\Responses\Resources;

use App\Billing\Claim;
use App\Billing\Exceptions\PaymentMethodError;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class ClaimResource extends ClientInvoice
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
            'client' => $this->resource->client,
            'clientPayer' => $this->resource->clientPayer,
            'payer' => $this->resource->clientPayer->payer,
            'payments' => $this->resource->payments,
            'balance' => $this->resource->amount - $this->resource->amount_paid,
            'claim_balance' => empty($this->resource->claim) ? 0.00 : $this->resource->claim->balance,
            'claim_status' => empty($this->resource->claim) ? Claim::NOT_SENT : $this->resource->claim->status,
            'claim' => $this->resource->claim,
        ]);
    }
}
