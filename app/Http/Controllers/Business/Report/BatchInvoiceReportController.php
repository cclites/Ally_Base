<?php

namespace App\Http\Controllers\Business\Report;

use App\Billing\ClientInvoice;
use App\Billing\View\InvoiceViewFactory;
use App\Billing\View\InvoiceViewGenerator;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientDropdownResource;
use App\Reports\BulkInvoiceReport;
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
     * @param BulkInvoiceReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, BulkInvoiceReport $report)
    {
        if ($request->filled('json')) {

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

            return response()->json($report->rows());
        }

        $clients = new ClientDropdownResource(Client::forRequestedBusinesses()->get());

        return view_component('batch-invoice-report', 'Batch Invoice Report', compact(['clients']), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Download PDF of all invoices.
     *
     * @param Request $request
     * @return Response
     */
    public function print(Request $request)
    {
        $invoiceIds = explode(",", $request->ids);
        $view = InvoiceViewFactory::HTML_VIEW;
        $html = "";

        foreach ($invoiceIds as $id) {
            $invoice = ClientInvoice::find($id);
            $strategy = InvoiceViewFactory::create($invoice, $view);
            $viewGenerator = new InvoiceViewGenerator($strategy);
            $html .= '<div style="page-break-after: always;">' . $viewGenerator->generateClientInvoice($invoice)->getContent() . '</div>';
        }

        $snappy = App::make('snappy.pdf');

        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoices.pdf"'
            )
        );
    }
}