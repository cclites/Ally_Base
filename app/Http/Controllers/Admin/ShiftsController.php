<?php
namespace App\Http\Controllers\Admin;

use App\GatewayTransaction;
use App\Http\Controllers\Controller;
use App\Reports\ShiftsReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftsController extends Controller
{
    public function data(Request $request)
    {
        $report = new ShiftsReport();
        $report->forRequestedBusinesses()->orderBy('checked_in_time');

        $this->addShiftReportFilters($report, $request);

        if ($request->input('export')) {
            return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                          ->download();
        }

        return $report->rows();
    }

    protected function addShiftReportFilters(ShiftsReport $report, Request $request)
    {
        $timezone = 'America/New_York';

        if ($request->has('business_id')) {
            $report->where('business_id', $request->business_id);
        }

        if ($request->has('import_id')) {
            $report->where('import_id', $request->import_id);
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $timezone);
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $timezone);
            $report->between($startDate, $endDate);
        }

        if ($request->has('transaction_id')) {
            $report->forTransaction(GatewayTransaction::findOrFail($request->input('transaction_id')));
        }

        if ($caregiver_id = $request->input('caregiver_id')) {
            $report->where('caregiver_id', $caregiver_id);
        }
        if ($client_id = $request->input('client_id')) {
            $report->where('client_id', $client_id);
        }
    }
}
