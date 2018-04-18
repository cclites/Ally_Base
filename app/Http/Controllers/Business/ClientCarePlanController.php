<?php

namespace App\Http\Controllers\Business;

use App\CarePlan;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Client;

class ClientCarePlanController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Client $client)
    {
        return $client->carePlans()->orderBy('name')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Client $client)
    {
        $data = $request->validate(
            [
                'name' => 'required',
                'activities' => 'required|array|min:1',
                'notes' => 'nullable',
            ],
            [
                'activities.required' => 'You must select at least 1 activity.',
                'activities.min' => 'You must select at least 1 activity.',
            ]
        );

        $plan = new CarePlan([
            'name' => $data['name'],
            'business_id' => $this->business()->id,
        ]);

        if (strlen($data['notes'])) {
            $plan->notes = $data['notes'];
        }

        if ($client->carePlans()->save($plan)) {

            $plan->activities()->sync($data['activities']);

            return new SuccessResponse('The care plan has been created.', $plan->load('activities')->toArray());

        }

        return new ErrorResponse(500, 'Unable to create care plan.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CarePlan  $carePlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client, CarePlan $carePlan)
    {
        if ($carePlan->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this care plan.');
        }

        $data = $request->validate(
            [
                'name' => 'required',
                'activities' => 'required|array|min:1',
                'notes' => 'nullable',
            ],
            [
                'activities.required' => 'You must select at least 1 activity.',
                'activities.min' => 'You must select at least 1 activity.',
            ]
        );

        $updates = [
            'name' => $data['name'],
            'notes' => strlen($data['notes']) ? $data['notes'] : null,
        ];

        if ($carePlan->update($updates)) {

            $carePlan->activities()->sync($data['activities']);

            return new SuccessResponse('The care plan has been updated.', $carePlan->load('activities')->toArray());

        }

        return new ErrorResponse(500, 'The care plan could not be saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CarePlan  $carePlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, CarePlan $carePlan)
    {
        if ($carePlan->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this care plan.');
        }

        if ($carePlan->delete()) {
            return new SuccessResponse('The care plan has been archived.');
        }
        return new ErrorResponse(500, 'The care plan could not be archived.');
    }
}
