<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Client;
use App\Reports\InvoiceSummaryBySalespersonReport;
use App\SalesPerson;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\InvoiceSummaryByMarketingReport;

use Illuminate\Http\Response;
use Log;

class InvoiceSummaryBySalespersonController extends BaseController
{
    public function index(Request $request, InvoiceSummaryBySalespersonReport $report){

        if ($request->filled('json') || $request->filled('print')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $data = $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->salesperson,
                        $request->client
                    )->rows();

            $totals = [
                'amount'=>$data->sum('amount'),
                'client' => filled($request->client) ? Client::find($request->client)->nameLastFirst() : 'All Clients',
                'salesperson' =>filled($request->salesperson) ? SalesPerson::find($request->salesperson)->fullName() : 'All Salespeople',
                'location' => Business::find($request->business)->name,
                'start' => $request->start,
                'end' => $request->end
            ];

            $data = $this->createSummary($data);

            if ($request->filled('print')) {
                return $this->printReport($data, $totals);
            }

            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        return view_component('invoice-summary-by-salesperson-report', 'Invoice Summary By Salesperson Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);

    }

    public function createSummary($data){
        $set = [];

        foreach($data as $item){
            $key = $item["salesperson"] . $item['client'] . $item['payer'];

            if(!isset($set[$key])){
                $set[$key] = [
                    'salesperson'=>$item['salesperson'],
                    'client' => $item['client'],
                    'amount'=>$item['amount'],
                    'payer' => $item['payer']
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
            }
        }

        return array_values($set);
    }

    /**
     * Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReport($data, $totals) : \Illuminate\Http\Response
    {
        $html = \View::make('business.reports.print.invoice_summary_by_salesperson',['data'=>$data, 'totals'=>$totals])->render();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoices_summary_by_salesperson.pdf"'
            )
        );
    }
}
