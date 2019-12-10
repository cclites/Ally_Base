<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimTransmissionFileResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\Contracts\TransmissionFileInterface
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
            'filename' => $this->resource->getFilename(),
            'created_at' => $this->resource->getDate()->toDateTimeString(),
            'results' => ClaimTransmissionFileResultResource::collection($this->resource->getResults()),
        ];
    }
}
