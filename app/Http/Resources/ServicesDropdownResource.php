<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ServicesDropdownResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'code' => $row->code,
                'display' => $row->name . ' ' . $row->code,
            ];
        })
            ->sortBy('display')
            ->values()
            ->toArray();
    }
}

