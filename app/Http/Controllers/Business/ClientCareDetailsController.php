<?php

namespace App\Http\Controllers\Business;

use App\Activity;
use App\CareDetails;
use App\CarePlan;
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

    /**
     * Download PDF of the clients care details.
     *
     * @param Client $client
     * @return ErrorResponse|Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function generatePdf(Client $client){

        $this->authorize('read', $client);

        $careDetails = $client->careDetails;
        $client->load('contacts', 'carePlans', 'carePlans.activities');

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

        $businessActivities = Activity::where('business_id', $client->business_id)
                                        ->orWhereNull('business_id')
                                        ->select('code', 'id', 'name')
                                        ->orderBy('id')
                                        ->get();

        $clientActivityIds = $client->carePlans->map(function(CarePlan $carePlan){
            return $carePlan->activities->map(function($activity){
                return $activity->id;
            });
        })->values()
            ->flatten(1)
            ->toArray();

        $physicianName = $client->contacts()->where('relationship', 'physician')->pluck('name')->first();

        $html = response(view('print.business.client_care_details', [
            'client' => $client,
            'businessActivities' => $businessActivities,
            'clientActivityIds' => $clientActivityIds,
            'physicianName' => $physicianName,
        ]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . standard_filename($client->name, 'care details', 'pdf') . '"'
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
