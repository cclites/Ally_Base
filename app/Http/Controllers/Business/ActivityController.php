<?php

namespace App\Http\Controllers\Business;

use App\Activity;
use App\Http\Requests\UpdateActivityRequest;
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
        $activities = Activity::forRequestedBusinesses()->get();
        
        return view('business.activities.index', compact('activities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\UpdateActivityRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(UpdateActivityRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Activity::class, $data]);

        $activity = new Activity($data);
        if ($this->business()->activities()->save($activity)) {
            return new CreatedResponse('The activity has been created.', ['id' => $activity->id]);
        }
        return new ErrorResponse(500, 'Error creating activity.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateActivityRequest $request
     * @param  \App\Activity $activity
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->authorize('update', $activity);
        $data = $request->filtered();

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
        $this->authorize('delete', $activity);

        if ($activity->delete()) {
            return new SuccessResponse('The activity has been deleted.');
        }
        return new ErrorResponse(500, 'Could not delete the activity.');
    }
}
