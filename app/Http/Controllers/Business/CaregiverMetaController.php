<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class CaregiverMetaController extends BaseController
{
    /**
     * Update a Caregiver meta / custom fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     * @throws \Exception
     */
    public function update(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);

        $customFields = $this->businessChain()->fields()->forCaregivers()->get();

        $rules = [];
        foreach ($customFields as $field) {
            $rules[$field->key] = ($field->required ? 'required' : 'nullable') . '|string';
        }

        \DB::beginTransaction();

        $data = $request->validate($rules);
        foreach ($data as $key => $value) {
            $caregiver->setMeta($key, $value);
        }

        \DB::commit();

        return new SuccessResponse('Caregiver custom fields have been saved.', $caregiver->meta);
    }
}
