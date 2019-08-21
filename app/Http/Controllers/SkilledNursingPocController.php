<?php

namespace App\Http\Controllers\Business;

use App\CareDetails;
use App\Http\Requests\UpdateClientCareDetailsRequest;
use App\Http\Requests\UpdateSkilledNursingPocRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Client;
use App\SkilledNursingPoc;
use http\Env\Request;

class SkilledNursingPocController extends BaseController
{

    public function index(Request $request){}


    /**
     * Update the client care details.
     *
     * @param UpdateSkilledNursingPocRequest $request
     * @param Client $client
     * @return SuccessResponse|ErrorResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateSkilledNursingPocRequest $request, Client $client)
    {

        $this->authorize('update', $client);

        if (empty($client->skilledNursingPoc)) {
            $client->skilledNursingPoc()->create([]);
        }

        $data = SkilledNursingPoc::convertFormData($request->validated());

        if ($client->skilledNursingPoc()->update($data)) {
            return new SuccessResponse('Client care needs have been saved successfully.', $client->fresh()->skilledNursingPoc);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save the client care needs.  Please try again.');

    }
}
