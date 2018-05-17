<?php

namespace App\Http\Controllers\Business;

use App\Responses\ErrorResponse;
use App\Timesheet;
use App\TimesheetEntry;
use App\Responses\SuccessResponse;

class TimesheetsController extends BaseController
{
    /**
     * View a Manual Timesheet
     *
     * @return void
     */
    public function edit()
    {
        return new ErrorResponse(400, "Not implemented", []);
    }

    /**
     * Approve a Manual Timesheet and convert to Shifts
     *
     * @return void
     */
    public function approve()
    {
        return new ErrorResponse(400, "Not implemented", []);
    }

}
