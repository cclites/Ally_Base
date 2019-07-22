<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\InvoiceSummaryByMarketingReport;

use Log;

class InvoiceSummaryByMarketingController extends BaseController
{
    public function index(Request $request, InvoiceSummaryByMarketingReport $report){

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->type,
                    $request->client,
                    $request->payer
                );

            $data = $report->rows();
            return response($data);
        }

        return view_component('invoice-summary-by-marketing-report', 'Invoice Summary By Marketing Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);

    }
}
