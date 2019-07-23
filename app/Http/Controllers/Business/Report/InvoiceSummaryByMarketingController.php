<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Client;
use App\SalesPerson;
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
            /*
            $this->authorize('read', Client::find($request->client));
            $this->authorize('read', SalesPerson::find($request->salesperson));
            */

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
            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        return view_component('invoice-summary-by-marketing-report', 'Invoice Summary By Marketing Report', [], [
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
}
