<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimTransmissionFileResultResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\Contracts\TransmissionFileResultInterface
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'service_date' => $this->resource->getServiceDate()->toDateTimeString(),
            'service_code' => $this->resource->getServiceCode(),
            'import_status' => $this->resource->getStatus(),
            'status_code' => $this->resource->getStatusCode(),
        ];
    }
}
