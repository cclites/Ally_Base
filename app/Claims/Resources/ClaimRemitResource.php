<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimRemitResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'amount_applied' => $this->amount,
            'amount_available' => $this->getAmountAvailable(),
            'business' => [
                'id' => $this->business->id,
                'name' => $this->business->name,
            ],
            'office_location' => $this->business->name,
            'notes' => $this->notes,
            'payer_name' => optional($this->payer)->name,
            'payment_type' => $this->payment_type,
            'reference' => $this->reference,
            'date' => $this->date->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'status' => $this->getStatus(),
        ];
    }
}
