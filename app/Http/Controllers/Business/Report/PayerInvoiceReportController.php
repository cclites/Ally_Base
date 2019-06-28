<?php

namespace App\Http\Controllers\Business\Report;

use App\Reports\PayerInvoiceReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Billing\Payer;
use App\Http\Resources\PayersDropdownResource;

class PayerInvoiceReportController extends Controller
{
    public function index(Request $request, PayerInvoiceReport $report){


        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            $report->forPayer($request->client_id ?? null)
                   ->forDates($request->payer_id ?? null);

            return response()->json($report->rows());
        }

        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->ordered()->get());

        return view_component('payer-invoice-report', 'Payer Invoice Report', compact['payers'], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}
