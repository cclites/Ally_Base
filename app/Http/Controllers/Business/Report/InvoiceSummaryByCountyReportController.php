<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use App\Business;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\InvoiceSummaryByCountyReport;
use App\Http\Controllers\Controller;

class InvoiceSummaryByCountyReportController extends BaseController
{
    public function index(Request $request, InvoiceSummaryByCountyReport $report){

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            if(filled($request->client)){
                $this->authorize('read', Client::find($request->client));
            }

            $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->client
                    );

            $data = $report->rows();

            $locationsTotals = [
                'amount' => $data->sum('amount'),
                'location' => Business::find($request->business)->name,
                'client' => filled($request->client) ? Client::find($request->client)->nameLastFirst() : null,
                'start' => $request->start,
                'end' => $request->end
            ];

            $rowTotals = $this->calculateTotals($data);

            $data = $this->createSummary($data);

            return response()->json(['data'=>$data, 'totals'=>$locationsTotals]);

        }

        return view_component(
            'invoice-summary-by-county',
            'Invoice By County Summary Report',
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
                    'clients'=> []
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
                $set[$key]['hours'] += $item['hours'];
            }
        }

        //add clients to each county
        foreach($data as $item){
            $key= $item['county'];
            $set[$key]['clients'][] = ['client_name'=>$item['client_name'], 'client_id'=>$item['client_id']];
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
}
