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
        if (!$this->businessHasCaregiver($request->caregiver_id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }
        
        if (!$this->businessHasClient($request->client_id)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }
        
        DB::beginTransaction();

        $timesheet = Timesheet::createWithEntries(
            $request->validated(),
            auth()->user(),
            activeBusiness()
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
        if (!$this->businessHasTimesheet($timesheet)) {
            return new ErrorResponse(403, 'You do not have access to this Timesheet.');
        }
        
        DB::beginTransaction();
        
        $timesheet = $timesheet->updateWithEntries($request->validated());

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
     *
     * @return void
     */
    public function deny(Timesheet $timesheet)
    {
        if (!$this->businessHasTimesheet($timesheet)) {
            return new ErrorResponse(403, 'You do not have access to this Timesheet.');
        }
        
        $timesheet->deny();

        return new SuccessResponse(
            'Timesheet has been denied.', 
            $timesheet->fresh()->load('caregiver', 'client')
        );
    }
}
