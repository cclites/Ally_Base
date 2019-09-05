<?php

namespace App\Claims\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ClaimableExpenseResource extends Resource
{
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
