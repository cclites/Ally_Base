<?php

namespace App\Http\Controllers\Business;

use App\CareDetails;
use App\Http\Requests\UpdateClientCareDetailsRequest;
use App\Http\Requests\UpdateSkilledNursingPocRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Client;
use App\SkilledNursingPoc;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use http\Env\Request;
use Illuminate\Http\Response;

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

    public function generatePdf(Client $client){

        $client = Client::where('id', 157)->with([
            'user',
            'medications',
            'careDetails',
            'carePlans',
            'business',
            'skilledNursingPoc',
            'addresses',
            'billingAddress',
            'goals',
            'notes' => function ($query) {
                return $query->orderBy('created_at', 'desc');
            },
            'contacts',
        ])->first();

        $client->careDetails->supplies_as_string = $this->snakeCaseArrayToUpperCaseString($client->careDetails->supplies);
        $client->careDetails->safety_measures_as_string = $this->snakeCaseArrayToUpperCaseString($client->careDetails->safety_measures);
        $client->careDetails->diet_as_string = $this->snakeCaseArrayToUpperCaseString($client->careDetails->diet);

        $image = asset('/images/background1.jpg');

        $html = response(view('poc.poc', ['client'=>$client, 'image'=>$image]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $client->nameLastFirst() . '_poc.pdf"'
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
