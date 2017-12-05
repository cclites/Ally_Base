<?php
namespace App\Http\Controllers\Admin;

use App\GatewayTransaction;
use App\Http\Controllers\Controller;
use App\Reports\ShiftsReport;
use Illuminate\Http\Request;

class ShiftsController extends Controller
{
    public function data(Request $request) {
        $report = new ShiftsReport();

        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');
            $report->between($startDate, $endDate);
        }
        if ($request->has('transaction_id')) {
            $report->forTransaction(GatewayTransaction::findOrFail($request->input('transaction_id')));
        }

        return $report->rows();
    }
}