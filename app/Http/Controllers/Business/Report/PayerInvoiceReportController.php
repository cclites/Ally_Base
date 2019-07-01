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
        //krioscare.test/business/reports/payer-invoice-report?start=06/20/2019&end=06/30/2019&confirmed=&charged=&payer=267&chain=11&json=1

        if ($request->filled('json')) {

            $report->isCharged($request->charged);
            $report->isConfirmed($request->confirmed);

            $report = $report->forDates($request->start, $request->end)
                      ->forPayer($request->payer);

            return response()->json($report);
        }

        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->ordered()->get());

        return view_component('payer-invoice-report', 'Payer Invoice Report', compact('payers'), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}
