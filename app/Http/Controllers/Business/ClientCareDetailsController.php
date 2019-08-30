<?php

namespace App\Http\Controllers\Business;

use App\CareDetails;
use App\Http\Requests\UpdateClientCareDetailsRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientCareDetailsController extends BaseController
{
    /**
     * Update the client care details.
     *
     * @param UpdateClientCareDetailsRequest $request
     * @param Client $client
     * @return SuccessResponse|ErrorResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateClientCareDetailsRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        if (empty($client->careDetails)) {
            $client->careDetails()->create([]);
        }

        $data = CareDetails::convertFormData($request->validated());
        if ($client->careDetails()->update($data)) {
            return new SuccessResponse('Client care needs have been saved successfully.', $client->fresh()->careDetails);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save the client care needs.  Please try again.');
    }

    protected function print($client){

        $client = Client::where('id', $client)->with([
            'careDetails',
            'skilledNursingPoc',
            'address'
        ])->first();

        $html = response(view('business.clients.client_care_details',['client'=>$client]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="testReport.pdf"'
            )
        );

    }
}
