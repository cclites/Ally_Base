<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ClientMetaController extends BaseController
{
    /**
     * Update a Client meta / custom fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     * @throws \Exception
     */
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $customFields = $client->business->chain->fields()->forClients()->get();

        $rules = [];
        foreach ($customFields as $field) {
            $rules[$field->key] = ($field->required ? 'required' : 'nullable');
        }

        \DB::beginTransaction();

        $data = $request->validate($rules);
        foreach ($data as $key => $value) {
            $client->setMeta($key, $value);
        }

        \DB::commit();

        return new SuccessResponse('Client custom fields have been saved.', $client->meta);
    }
}
