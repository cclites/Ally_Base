<?php

namespace App\Http\Requests;

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
        $data['key'] = preg_replace('/[^A-Za-z0-9]/', '', snake_case($data['label']));
        return $data;
    }
}
