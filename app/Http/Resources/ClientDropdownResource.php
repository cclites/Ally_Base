<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

use Log;

class ClientDropdownResource extends ResourceCollection
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
                'nameLastFirst' => $row->nameLastFirst,
            ];
        })
        ->sortBy('nameLastFirst')
        ->values()
        ->toArray();
    }
}
