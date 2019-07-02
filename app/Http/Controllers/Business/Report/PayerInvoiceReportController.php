<?php

namespace App\Http\Controllers\Business\Report;

use App\Reports\PayerInvoiceReport;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Billing\Payer;
use App\Http\Resources\PayersDropdownResource;

use Log;

class PayerInvoiceReportController extends Controller
{
    public function index(Request $request, PayerInvoiceReport $report){

        Log::info(json_encode($request->all()));

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start_date,
                        $request->end_date,
                        intval($request->payer_id),
                        intval($request->business_id),
                        $request->confirmed,
                        $request->charged
                    );

            Log::info($report->rows());

            return $report->rows();
        }

        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->ordered()->get());

        return view_component('payer-invoice-report', 'Payer Invoice Report', compact('payers'), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}
