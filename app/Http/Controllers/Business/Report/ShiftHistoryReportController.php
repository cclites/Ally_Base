<?php

namespace App\Http\Controllers\Business\Report;

use App\Billing\GatewayTransaction;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Business;
use App\Http\Controllers\Business\BaseController;
use App\Reports\ShiftHistoryReport;
use App\Reports\ShiftsReport;
use App\Responses\ErrorResponse;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        if ($request->filled('json')) {
            $report->setTimezone(auth()->user()->role->getTimezone())
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
                    $request->flags
                );

            if ($report->count() > 1000) {
                // Limit shift history to 1000 shifts for performance reasons
                return new ErrorResponse(400, 'There are too many shifts to display.  Please adjust your filters and re-run.');
            }
    
            if ($request->input('export')) {
                return $report->setDateFormat('m/d/Y g:i A', $this->business()->timezone ?? 'America/New_York')
                              ->download();
            }
    
            return $report->rows();
        }

        return view_component('business-shift-report', 'Shift History', [
            'admin' => (int) is_admin(),
            'imports' => is_admin() ? \App\Import::orderBy('id', 'DESC')->get()->toArray() : [],
            'autoload' => $request->autoload ? 1 : 0,
            'activities' => $this->business()->allActivities(), // TODO: replace this
        ], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index'),
        ]);
    }
}