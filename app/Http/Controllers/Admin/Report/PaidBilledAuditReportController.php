<?php

namespace App\Http\Controllers\Admin\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reports\PaidBilledAuditReport;
use App\SalesPerson;
use App\Business;

class PaidBilledAuditReportController extends Controller
{
    public function index(Request $request, PaidBilledAuditReport $report){

        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $this->authorize('read', Business::find($request->business));

            $data = $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->salesperson
                )->rows();

            $totals = [
                'amount'=>$data->sum('amount'),
                'salesperson' =>filled($request->salesperson) ? SalesPerson::find($request->salesperson)->fullName() : 'All Salespeople',
                'location' => Business::find($request->business)->name,
                'start' => $request->start,
                'end' => $request->end
            ];

            $data = $this->createSummary($data);
            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        return view_component('paid-billed-audit-report', 'Paid Billed Audit Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function createSummary($data){

        $set = [];

        foreach($data as $item){

            $key = $item["location"] . $item["salesperson"] . $item["date"] . $item['service'] . $item['caregiver'];

            if(!isset($set[$key])){
                $set[$key] = [
                    'location'=>$item['location'],
                    'client'=> $item['client'],
                    'caregiver'=>$item['caregiver'],
                    'service'=>$item["service"],
                    'hours' => $item['hours'],
                    'billable' =>$item['billable'],
                    'amount' => $item['amount']
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
                $set[$key]['hours'] += $item['hours'];
                $set[$key]['billable'] += $item['billable'];
            }

        }


    }
}
