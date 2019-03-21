<?php
namespace App\Responses\Resources;

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
            'claim_balance' => 0.00,
        ]);
    }
}
