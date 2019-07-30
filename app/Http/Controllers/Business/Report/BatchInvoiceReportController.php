<?php

namespace App\Http\Controllers\Business\Report;

use App\Billing\ClientInvoice;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientDropdownResource;
use App\Reports\BatchInvoiceReport;
use Illuminate\Http\Request;
use App\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Response;

class BatchInvoiceReportController extends Controller
{
    /**
     * Get the Batch Invoice Report.
     *
     * @param Request $request
     * @param BatchInvoiceReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, BatchInvoiceReport $report)
    {
        if ($request->filled('json') || $request->filled('print')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client,
                    $request->type,
                    $request->active
                );

            if ($request->filled('print')) {
                return $report->print();
            }

            return response()->json($report->rows());
        }

        $clients = new ClientDropdownResource(Client::forRequestedBusinesses()->get());

        return view_component('batch-invoice-report', 'Batch Invoice Report', compact(['clients']), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}