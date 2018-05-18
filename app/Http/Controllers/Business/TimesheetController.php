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
     * Returns form for creating a manual timesheet.
     *
     * @return void
     */
    public function create()
    {
        $caregiver = '{}';
        $mode = 'business';
        if (auth()->user()->role_type == 'caregiver') {
            $caregiver = auth()->user();
            $mode = 'caregiver';
        }

        $business = activeBusiness();
        $activities = $business->allActivities();
        $caregivers = $this->caregiverClientList($business);
        $success = request()->success == 1;

        return view("$mode.timesheet", compact(
            'success',
            'caregiver',
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
     * View a Manual Timesheet.
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
        $caregivers = $this->caregiverClientList($timesheet->business);
        
        return view('business.timesheet', compact(
            'timesheet',
            'activities',
            'caregivers'
        ));
    }

    /**
     * Update a Manual Timesheet and handle approval and converting to Shifts.
     * Undocumented function
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
        
        if ($request->deny == 1) {
            $timesheet->deny();
 
            return new SuccessResponse(
                'Timesheet has been denied.', 
                $timesheet->fresh()->load('caregiver', 'client')
            );
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
