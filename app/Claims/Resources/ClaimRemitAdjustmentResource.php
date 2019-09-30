<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimRemitAdjustmentResource extends Resource
{
    /**
     * The resource instance.
     *
     * @var \App\Claims\ClaimRemitAdjustment
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
        return parent::toArray($request);
    }
}
