<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateTimesheetsRequest;
use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;

class TimesheetController extends Controller
{
    /**
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return auth()->user()->role;
    }

    /**
     * Returns form for creating a manual timesheet.
     *
     * @return void
     */
    public function create()
    {
        $caregiver = '{}';
        if (auth()->user()->role_type == 'caregiver') {
            $caregiver = auth()->user();
        }

        $business = activeBusiness();
        $activities = $business->allActivities();
        $caregivers = $this->caregiverClientList($business);
        $success = request()->success == 1;

        return view('caregivers.timesheet', compact(
            'success',
            'caregiver',
            'caregivers', 
            'activities'
        ));
    }

    /**
     * Handles submission of Timesheets.
     *
     * @return void
     */
    public function store(CreateTimesheetsRequest $request)
    {
        if (auth()->user()->role_type == 'caregiver' && $request->caregiver_id != auth()->user()->id) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $timesheet = Timesheet::make(array_diff_key($request->validated(), ['entries' => [] ]));
        $timesheet->creator_id = auth()->user()->id;
        $timesheet->business_id = activeBusiness()->id;
        $timesheet->save();
        
        foreach($request->validated()['entries'] as $item) {
            if ($entry = $timesheet->entries()->create(array_diff_key($item, ['activities' => [], 'duration' => '', 'start_time' => '', 'end_time' => '', 'date' => '' ]))) {
                $entry->activities()->sync($item['activities']);
            } 
        }

        return new SuccessResponse('Your timesheet has been submitted for approval.', ['timesheet' => $timesheet->fresh()->toArray()]);
    }

    /**
     * Gets list of all the businesses caregivers with attached clients
     * in simple array.  Intended for smart dropdowns.
     *
     * @return Array
     */
    public function caregiverClientList($business)
    {
        return $business->caregivers()->with('clients')->get()->map(function ($cg) {
            return [
                'id' => $cg->id,
                'name' => $cg->nameLastFirst,
                'clients' => $cg->clients->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->nameLastFirst,
                        'caregiver_hourly_rate' => $c->pivot->caregiver_hourly_rate,
                        'provider_hourly_fee' => $c->pivot->provider_hourly_fee,
                    ];
                }),
            ];
        });
    }
}
