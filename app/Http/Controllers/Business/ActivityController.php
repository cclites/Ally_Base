<?php

namespace App\Http\Controllers\Business;

use App\Activity;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidActivityCode;
use Illuminate\Http\Request;

class ActivityController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $activities = $this->business()->allActivities();

        if (request()->expectsJson()) {
            return response()->json($activities);
        }
        
        return view('business.activities.index', compact('activities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'numeric', new ValidActivityCode($this->business()->id)],
            'name' => 'required'
        ]);

        $activity = new Activity($data);
        if ($this->business()->activities()->save($activity)) {
            return new CreatedResponse('The activity has been created.', ['id' => $activity->id]);
        }
        return new ErrorResponse(500, 'Error creating activity.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Activity $activity)
    {
        if ($activity->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this activity.');
        }

        $data = $request->validate([
            'code' => ['required', 'numeric', new ValidActivityCode($this->business()->id, $activity->id)],
            'name' => 'required'
        ]);

        if ($activity->update($data)) {
            return new SuccessResponse('The activity has been saved.');
        }
        return new ErrorResponse(500, 'Could not save the activity.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Activity $activity)
    {
        if ($activity->business_id != $this->business()->id) {
            return new ErrorResponse(403, 'You do not have access to this activity.');
        }

        if ($activity->delete()) {
            return new SuccessResponse('The activity has been deleted.');
        }
        return new ErrorResponse(500, 'Could not delete the activity.');
    }
}
