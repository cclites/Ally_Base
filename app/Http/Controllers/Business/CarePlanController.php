<?php

namespace App\Http\Controllers\Business;

use App\CarePlan;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class CarePlanController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $plans = $this->business()->carePlans()->orderBy('name')->get();

        if ($request->expectsJson()) {
            return $plans;
        }

        return view('business.care_plans.index', compact('plans'));
    }

    public function create()
    {
        $activities = $this->business()->allActivities();
        return view('business.care_plans.create', compact('activities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'name' => 'required',
                'activities' => 'required|array|min:1',
            ],
            [
                'activities.required' => 'You must select at least 1 activity.',
                'activities.min' => 'You must select at least 1 activity.',
            ]
        );

        $plan = new CarePlan(['name' => $data['name']]);
        if ($this->business()->carePlans()->save($plan)) {
            $plan->activities()->sync($data['activities']);
            return new SuccessResponse('The care plan has been created.', $plan->toArray(), route('business.care_plans.show', [$plan->id]));
        }
        return new ErrorResponse(500, 'Unable to create care plan.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CarePlan  $carePlan
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, CarePlan $carePlan)
    {
        if ($carePlan->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this care plan.');
        }

        $carePlan->load('activities');

        if ($request->expectsJson()) {
            return $carePlan;
        }

        $activities = $this->business()->allActivities();
        return view('business.care_plans.show', ['plan' => $carePlan, 'activities' => $activities]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CarePlan  $carePlan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CarePlan $carePlan)
    {
        if ($carePlan->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this care plan.');
        }

        $data = $request->validate(
            [
                'name' => 'required',
                'activities' => 'required|array|min:1',
            ],
            [
                'activities.required' => 'You must select at least 1 activity.',
                'activities.min' => 'You must select at least 1 activity.',
            ]
        );

        if ($carePlan->update(['name' => $data['name']])) {
            $carePlan->activities()->sync($data['activities']);
            return new SuccessResponse('The care plan has been updated.', $carePlan->toArray());
        }
        return new ErrorResponse(500, 'The care plan could not be saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CarePlan  $carePlan
     * @return \Illuminate\Http\Response
     */
    public function destroy(CarePlan $carePlan)
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
