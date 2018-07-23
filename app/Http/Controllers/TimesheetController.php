<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateTimesheetsRequest;
use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;
use DB;

class TimesheetController extends Controller
{
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
        $caregivers = $business->caregiverClientList($business);
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
     * @param \App\Http\Requests\CreateTimesheetsRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function store(CreateTimesheetsRequest $request)
    {
        if (auth()->user()->role_type == 'caregiver' && $request->caregiver_id != auth()->user()->id) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        if (! activeBusiness()->allows_manual_shifts) {
            return new ErrorResponse(403, 'Forbidden.');
        }

        DB::beginTransaction();

        $timesheet = Timesheet::createWithEntries(
            $request->validated(),
            auth()->user(),
            activeBusiness()
        );

        if ($timesheet === false) {
            DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred while saving the Timesheet.');
        }

        DB::commit();
        return new SuccessResponse(
            'Your timesheet has been submitted for approval.', 
            ['timesheet' => $timesheet], 
            route('caregivers.timesheet') . '?success=1' 
        );
    }
}
