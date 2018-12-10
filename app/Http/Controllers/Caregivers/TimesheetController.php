<?php

namespace App\Http\Controllers\Caregivers;

use App\Http\Requests\CaregiverTimesheetRequest;
use Illuminate\Http\Request;
use App\Http\Requests\CreateTimesheetRequest;
use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;
use DB;

class TimesheetController extends BaseController
{
    public function index()
    {
        $caregiver = $this->caregiver();
        $timesheets = Timesheet::where('caregiver_id', $this->caregiver()->id)->orderBy('id', 'DESC')->get();
        return view('caregivers.timesheet.index', compact('caregiver', 'timesheets'));
    }

    /**
     * Returns form for creating a manual timesheet.
     *
     * @return void
     */
    public function create()
    {
        $caregiver = $this->caregiver();
        $business = activeBusiness();
        $activities = $business->allActivities();
        $caregivers = $business->caregiverClientList($business);
        $success = request()->success == 1;

        return view('caregivers.timesheet.create', compact(
            'success',
            'caregiver',
            'caregivers', 
            'activities'
        ));
    }

    public function show(Timesheet $timesheet)
    {
        $caregiver = $this->caregiver();
        $business = $timesheet->business;
        $activities = $business->allActivities();
        $caregivers = $business->caregiverClientList($business);

        return view('caregivers.timesheet.show', compact(
            'timesheet',
            'caregiver',
            'caregivers',
            'activities'
        ));
    }

    /**
     * Handles submission of Timesheets.
     *
     * @param \App\Http\Requests\CreateTimesheetRequest $request
     * @return \App\Responses\ErrorResponse|\App\Responses\SuccessResponse
     * @throws \Exception
     */
    public function store(CreateTimesheetRequest $request)
    {
        $data = $request->validated();
        $business = $request->getBusiness();
        $this->authorize('create', [Timesheet::class, $data]);

        if (! $business->allows_manual_shifts) {
            return new ErrorResponse(403, 'This business does not allow manual timesheets to be submitted.');
        }

        DB::beginTransaction();

        $timesheet = Timesheet::createWithEntries(
            $data,
            auth()->user()
        );

        if ($timesheet === false) {
            DB::rollBack();
            return new ErrorResponse(500, 'An unexpected error occurred while saving the Timesheet.');
        }

        DB::commit();
        return new SuccessResponse(
            'Your timesheet has been submitted for approval.', 
            ['timesheet' => $timesheet], 
            route('timesheets.index') . '?success=1'
        );
    }

    public function update(CreateTimesheetRequest $request, Timesheet $timesheet)
    {
        if ($request->caregiver_id != $this->caregiver()->id || $timesheet->caregiver_id != $this->caregiver()->id) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        if ($timesheet->approved_at || $timesheet->denied_at) {
            return new ErrorResponse(400, 'This timesheet has already been ' . ($timesheet->approved_at ? 'approved' : 'denied' . '.'));
        }

        $timesheet->updateWithEntries($request->validated());

        return new SuccessResponse(
            'Your timesheet has been updated.',
            ['timesheet' => $timesheet],
            route('timesheets.index') . '?success=1'
        );
    }
}
