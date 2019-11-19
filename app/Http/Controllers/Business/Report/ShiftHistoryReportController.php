<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\Http\Controllers\Business\BaseController;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\ShiftHistoryItemResource;
use App\Reports\ShiftHistoryReport;
use App\Responses\ErrorResponse;
use App\Reports\ShiftsReport;
use Illuminate\Http\Request;
use App\Shift;

class ShiftHistoryReportController extends BaseController
{
    /**
     * Get the Shift History Report
     *
     * @param Request $request
     * @param ShiftHistoryReport $report
     * @return ShiftsReport|ErrorResponse|\Illuminate\Contracts\View\Factory|\Illuminate\Support\Collection|\Illuminate\View\View|void
     * @throws \Exception
     */
    public function index(Request $request, ShiftHistoryReport $report)
    {
        $timezone = auth()->user()->role->getTimezone();

        if ($request->filled('json')) {
            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start_date,
                    $request->end_date,
                    $request->import_id,
                    $request->client_id,
                    $request->caregiver_id,
                    $request->payment_method,
                    $request->status,
                    $request->confirmed,
                    $request->client_type,
                    $request->flag_type,
                    $request->flags,
                    $request->service_id
                );

            if ($report->count() > 1000) {
                // Limit shift history to 1000 shifts for performance reasons
                return new ErrorResponse(400, 'There are too many shifts to display.  Please adjust your filters and re-run.');
            }

            if ($request->input('export')) {
                return $report->setDateFormat('m/d/Y g:i A', $timezone)
                    ->download();
            }

            return $report->rows();
        }

        return view_component('business-shift-report', 'Shift History', [
            'admin' => (int)is_admin(),
            'imports' => is_admin() ? \App\Import::orderBy('id', 'DESC')->where('type', 'shift')->get()->toArray() : [],
            'autoload' => $request->autoload ? 1 : 0,
            'activities' => $this->business()->allActivities(), // TODO: replace this
        ], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index'),
        ]);
    }

    /**
     * Re-load single shift for the shift history report.
     *
     * @param Shift $shift
     * @return ShiftHistoryItemResource
     */
    public function reloadShift(Shift $shift)
    {
        return new ShiftHistoryItemResource($shift);
    }
}