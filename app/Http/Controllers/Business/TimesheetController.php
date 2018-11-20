<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;
use App\Http\Requests\ApproveTimesheetRequest;
use App\Shift;
use DB;
use App\Http\Requests\CreateTimesheetsRequest;

class TimesheetController extends BaseController
{
    /**
     * Returns form for creating a Timesheet.
     *
     * @return void
     */
    public function create()
    {
        $business = activeBusiness();
        $activities = $business->allActivities();
        $caregivers = $business->caregiverClientList($business);
        $success = request()->success == 1;

        return view("business.timesheet", compact(
            'success',
            'caregivers', 
            'activities'
        ));
    }

    /**
     * Handles submission of Timesheets.
     *
     * @param CreateTimesheetsRequest $request
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTimesheetsRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('create', [Timesheet::class, $data]);

        DB::beginTransaction();

        $timesheet = Timesheet::createWithEntries(
            $data,
            auth()->user()
        );

        if ($timesheet !== false) {
            if ($request->approve == 1) {
                if ($timesheet->createShiftsFromEntries()) {
                    $timesheet->approve();
                    
                    DB::commit();

                    return new SuccessResponse(
                        'Timesheet has been saved and converted into Shifts', 
                        $timesheet, 
                        route('business.timesheet', ['timesheet' => $timesheet])
                    );
                }
            } else {
                DB::commit();

                return new SuccessResponse(
                    'Timesheet has been saved.', 
                    $timesheet, 
                    route('business.timesheet', ['timesheet' => $timesheet])
                );
            }
        }

        DB::rollBack();
        return new ErrorResponse(500, 'An unexpected error occurred while saving the Timesheet.');
    }

    /**
     * View/edit a Timesheet.
     *
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Timesheet $timesheet)
    {
        if (!$this->businessHasTimesheet($timesheet)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $timesheet->load('caregiver', 'client');
        $activities = activeBusiness()->allActivities();
        $caregivers = $timesheet->business->caregiverClientList($timesheet->business);
        
        return view('business.timesheet', compact(
            'timesheet',
            'activities',
            'caregivers'
        ));
    }

    /**
     * Update a Timesheet and handle approval and converting to Shifts.
     *
     * @param ApproveTimesheetRequest $request
     * @param Timesheet $timesheet
     * @return \Illuminate\Http\Response
     */
    public function update(ApproveTimesheetRequest $request, Timesheet $timesheet)
    {
        $this->authorize('update', $timesheet);
        $data = $request->filtered();
        
        DB::beginTransaction();
        
        $timesheet = $timesheet->updateWithEntries($data);

        if ($timesheet != false) {
            if ($request->approve == 1) {
                if ($timesheet->createShiftsFromEntries()) {
                    $timesheet->approve();
                    
                    DB::commit();
                    return new SuccessResponse('Timesheet has been approved.', $timesheet);
                }
            } else {
                DB::commit();
                return new SuccessResponse('Timesheet was updated.', $timesheet);
            }
        }

        DB::rollBack();
        return new ErrorResponse(500, 'An unexpected error occurred while saving the Timesheet.');
    }

    /**
     * Denies the Manual Timesheet.
     * @param \App\Timesheet $timesheet
     * @return \App\Responses\SuccessResponse
     */
    public function deny(Timesheet $timesheet)
    {
        $this->authorize('update', $timesheet);

        $timesheet->deny();

        return new SuccessResponse(
            'Timesheet has been denied.', 
            $timesheet->fresh()->load('caregiver', 'client')
        );
    }
}
