<?php

namespace App\Http\Controllers\Business;

use App\CareDetails;
use App\Http\Requests\UpdateClientCareDetailsRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Client;
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

        $data = CareDetails::convertFormData($request->validated());

        if ($client->careDetails()->updateOrCreate(['client_id' => $client->id], $data)) {
            return new SuccessResponse('Client care needs have been saved successfully.', $client->fresh()->careDetails);
        }

        return new ErrorResponse(500, 'An unexpected error occurred while trying to save the client care needs.  Please try again.');
    }

    public function generatePdf(Client $client){

        $this->authorize('read', $client);

        $careDetails = $client->careDetails;

        if(!$careDetails){
            return new ErrorResponse(500, 'You must first create and save a Care Details plan before printing.');
        }

        if($careDetails->supplies){
            $client->careDetails->supplies_as_string = $this->snakeCaseArrayToUpperCaseString($careDetails->supplies);
        }

        if($careDetails->safety_measures){
            $client->careDetails->safety_measures_as_string = $this->snakeCaseArrayToUpperCaseString($careDetails->safety_measures);
        }

        if($careDetails->diet){
            $client->careDetails->diet_as_string = $this->snakeCaseArrayToUpperCaseString($careDetails->diet);
        }

        $image = asset('/images/background1.jpg');
        $html = response(view('business.clients.client_care_details', ['client'=>$client, 'image'=>$image]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $client->nameLastFirst() . '_care_details.pdf"'
            )
        );
    }

    public function snakeCaseArrayToUpperCaseString($array){

        $temp='';

        foreach($array as $item){
            $temp .= ucwords(str_replace("_", " ", $item)) . " ";
        }

        return $temp;
    }
}
