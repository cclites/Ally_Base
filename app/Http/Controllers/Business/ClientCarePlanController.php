<?php

namespace App\Http\Controllers\Business;

use App\CarePlan;
use App\Responses\ConfirmationResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Client;

class ClientCarePlanController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Client $client)
    {
        $this->authorize('read', $client);

        return $client->carePlans()
            ->withCount('futureSchedules')
            ->orderBy('name')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

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
            'business_id' => $client->business_id,
        ]);

        if (isset($data['notes']) && strlen($data['notes'])) {
            $plan->notes = $data['notes'];
        }

        if ($client->carePlans()->save($plan)) {

            $plan->activities()->sync($data['activities']);

            return new SuccessResponse('The group has been created.', $plan->load('activities')->toArray(), '.');

        }

        return new ErrorResponse(500, 'Unable to create group.');
    }

    /**
     * @param \App\Client $client
     * @param \App\CarePlan $carePlan
     * @return \App\CarePlan
     */
    public function show(Client $client, CarePlan $carePlan)
    {
        $this->authorize('read', $client);

        $carePlan->future_schedules_count = $carePlan->futureSchedules()->count();
        return $carePlan;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @param  \App\CarePlan $carePlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client, CarePlan $carePlan)
    {
        $this->authorize('update', $client);

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

            return new SuccessResponse('The group has been updated.', $carePlan->load('activities')->toArray(), '.');

        }

        return new ErrorResponse(500, 'The group could not be saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Client $client
     * @param  \App\CarePlan $carePlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, CarePlan $carePlan)
    {
        $this->authorize('update', $client);

        if ($carePlan->delete()) {
            $carePlan->removeFromFutureSchedules();

            return new SuccessResponse('The group has been archived.', [], '.');
        }
        return new ErrorResponse(500, 'The group could not be archived.');
    }
}
