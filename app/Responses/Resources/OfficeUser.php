<?php
namespace App\Responses\Resources;

use Illuminate\Http\Resources\Json\Resource;

class OfficeUser extends Resource
{
    /**
     * @var \App\OfficeUser
     */
    public $resource;

    public function toArray($request)
    {
        $array = $this->resource->attributesToArray();
        return $array + [
            'businesses' => $this->resource->businesses()->pluck('business_id')->toArray(),
        ];
    }
}