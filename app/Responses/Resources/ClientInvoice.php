<?php
namespace App\Responses\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class ClientInvoice extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Billing\ClientInvoice
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
        return $this->resource->attributesToArray() + [
            'client' => $this->whenLoaded('client'),
            'payer' => $this->whenLoaded('clientPayer', function() {
                return $this->clientPayer->payer;
            }),
            'items' => $this->whenLoaded('items', function() {
                return $this->groupItems($this->resource->items)->toArray();
            }),
            'payments' => $this->whenLoaded('payments'),
        ];
    }
}
