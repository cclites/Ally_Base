<?php

namespace App\Responses\Resources;

use App\Billing\ClientInvoiceItem;
use Carbon\Carbon;
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
            'items' => $this->groupItems($this->resource->items)->toArray(),
            'payments' => $this->resource->payments->toArray(),
        ];
    }
}
