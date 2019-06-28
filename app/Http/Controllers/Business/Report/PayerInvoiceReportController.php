<?php

namespace App\Http\Controllers\Business\Report;

use App\Reports\PayerInvoiceReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayerInvoiceReportController extends Controller
{
    public function index(Request $request, PayerInvoiceReport $report){


        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            $report->forPayer($request->client_id ?? null)
                ->forDates($request->payer_id ?? null);

            return response()->json($report->rows());
        }

        return view_component('payer-invoice-report', 'Payer Invoice Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}
