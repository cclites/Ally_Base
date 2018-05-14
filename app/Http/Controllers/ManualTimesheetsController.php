<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateManualTimesheetsRequest;
use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;

class ManualTimesheetsController extends Controller
{
    /**
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return auth()->user()->role;
    }

    /**
     * Returns form for creating a manual shift.
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

        return view('caregivers.manual_timesheets', compact(
            'caregiver',
            'caregivers', 
            'activities'
        ));
    }

    /**
     * Handles submission of manual shifts.
     *
     * @return void
     */
    public function store(CreateManualTimesheetsRequest $request)
    {
        if (auth()->user()->role_type == 'caregiver' && $request->caregiver_id != auth()->user()->id) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $timesheet = Timesheet::make(array_diff_key($request->validated(), ['shifts' => [] ]));
        $timesheet->creator_id = auth()->user()->id;
        $timesheet->business_id = activeBusiness()->id;
        $timesheet->save();
        
        foreach($request->validated()['shifts'] as $shift) {
            if ($entry = $timesheet->entries()->create(array_diff_key($shift, ['activities' => [] ]))) {
                $entry->activities()->sync($shift['activities']);
            } 
        }

        return new SuccessResponse('Your timesheet has been submitted for approval.', ['timesheet' => $timesheet->fresh()->toArray()]);
    }

    /**
     * View a Manual Timesheet (for Business)
     *
     * @return void
     */
    public function view()
    {
        return new ErrorResponse(400, "Not implemented", []);
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
