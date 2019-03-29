<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Arr;

class CommunicationLogResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (! is_admin()) {
            return [];
        }

        return Arr::except($this->resource->toArray(), 'body');
    }
}
