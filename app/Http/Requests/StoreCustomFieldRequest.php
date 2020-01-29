<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;

class StoreCustomFieldRequest extends UpdateCustomFieldRequest
{
    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered()
    {
        $data = parent::filtered();
        $data['key'] = preg_replace('/[^A-Za-z0-9]/', '', Str::snake($data['label']));
        return $data;
    }
}
