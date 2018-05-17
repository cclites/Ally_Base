<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;
use App\Http\Requests\ApproveTimesheetRequest;
use App\Shift;
use DB;

class TimesheetController extends BaseController
{
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
        
        return view('business.timesheet', compact(
            'timesheet',
            'activities'
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
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }
        
        if ($request->deny == 1) {
            $timesheet->deny();
 
            return new SuccessResponse(
                'Timesheet has been denied.', 
                $timesheet->fresh()->load('caregiver', 'client')
            );
        }

        DB::beginTransaction();

        $timesheet->update(array_diff_key($request->validated(), ['entries' => [] ]));

        $timesheet->entries()->delete();
        foreach($request->validated()['entries'] as $item) {
            if ($entry = $timesheet->entries()->create(array_diff_key($item, ['activities' => [], 'duration' => '', 'start_time' => '', 'end_time' => '', 'date' => '' ]))) {
                $entry->activities()->sync($item['activities']);
            }
        }

        if ($request->approve == 1) {
            if ($this->convertTimesheetToShift($timesheet->fresh())) {
                $timesheet->approve();
                
                DB::commit();
                return new SuccessResponse(
                    'Timesheet has been approved.', 
                    $timesheet->fresh()->load('caregiver', 'client')
                );
            }
        } else {
            DB::commit();
            return new SuccessResponse(
                'Timesheet was updated.', 
                $timesheet->fresh()->load('caregiver', 'client')
            );
        }

        DB::rollBack();
        return new ErrorResponse(500, 'An unexpected error occurred while converting the Timesheet to Shifts.');
    }

    /**
     * Helper function to convert a Timesheet to actual Shifts.
     *
     * @param \App\Timesheet $timesheet
     * @return void
     */
    public function convertTimesheetToShift($timesheet)
    {
        foreach($timesheet->entries as $entry) {
            $data['checked_in_time'] = $entry['checked_in_time'];
            $data['checked_out_time'] = $entry['checked_out_time'];
            $data['mileage'] = $entry['mileage'];
            $data['other_expenses'] = $entry['other_expenses'];
            $data['caregiver_comments'] = $entry['caregiver_comments'];
            $data['caregiver_rate'] = $entry['caregiver_rate'];
            $data['provider_fee'] = $entry['provider_fee'];

            $data['timesheet_id'] = $timesheet->id;
            $data['caregiver_id'] = $timesheet->caregiver_id;
            $data['client_id'] = $timesheet->client_id;
            $data['business_id'] = $timesheet->business_id;
            $data['checked_in_method'] = Shift::METHOD_TIMESHEET;
            $data['checked_out_method'] = Shift::METHOD_TIMESHEET;
            $data['hours_type'] = 'default';
            $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
            $data['verified'] = false;

            if ($shift = Shift::create($data)) {
                $shift->activities()->sync($entry->activities);
            } else {
                return false;
            }
        }

        return true;
    }
}
