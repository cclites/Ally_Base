<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\Business;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\InvoiceSummaryByCountyReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


class InvoiceSummaryByCountyReportController extends BaseController
{
    public function index(Request $request, InvoiceSummaryByCountyReport $report){

        if ($request->filled('json') || $request->filled('print')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->client
                    );

            $data = $report->rows()->sortBy('county');

            $totals = [
                'amount' => $data->sum('amount'),
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

        return view_component(
            'invoice-summary-by-county',
            'Invoice By County Summary',
            [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }

    public function createSummary($data){

        $set = [];

        foreach($data as $item){

            $key = $item["county"];

            if(!isset($set[$key])){
                $set[$key] = [
                  'county'=>$item['county'],
                    'hours' => $item['hours'],
                  'amount'=>$item['amount'],
                    'clients'=>[]
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
                $set[$key]['hours'] += $item['hours'];
            }
        }

        //add clients to each county
        $temp = [];

        foreach($data as $item){

            $key= $item['county'];

            if(!in_array($item['client_id'], $temp)){
                $temp[] = $item['client_id'];
                $set[$key]['clients'][] = ['client_name'=>$item['client_name'], 'client_id'=>$item['client_id']];
           }
        }

        return array_values($set);
    }

    public function calculateTotals($data){

        $set = [];

        foreach($data as $item){

            $key = $item["county"];

            if(!isset($set[$key])){
                $set[$key] = [
                    'county'=>$item['county'],
                    'amount'=>$item['amount']
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
            }

        }

    }

    /**
     * Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReport($data, $totals) : \Illuminate\Http\Response
    {
        $html = response(view('business.reports.print.invoice_summary_by_county',['data'=>$data, 'totals'=>$totals]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="invoices_summary_by_county.pdf"'
            )
        );

    }
}
