<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VisitEditActionResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request )
    {
        return $this->collection->map( function( $row ){

            return [

                'id'          => $row->id,
                'code'        => $row->code,
                'description' => $row->description,
            ];
        })->sortBy( 'code' )
        ->values()
        ->toArray();
    }
}

