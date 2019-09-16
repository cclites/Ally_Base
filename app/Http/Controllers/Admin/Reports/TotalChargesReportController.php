<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Billing\Payer;
use App\Business;
use App\Client;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Reports\TotalChargesReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TotalChargesReportController extends Controller
{
    public function index(Request $request, TotalChargesReport $report)
    {
        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end
                );

            $data = $report->rows();
            $totals = [
                'amount'=>$data->sum('amount')
            ];

            $data = $this->createSummary($report->rows());

            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        return view_component(
            'total-charges-report',
            'Total Charges Report',
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

            $key = $item["location"];

            if(!isset($set[$key])){
                $set[$key] = [
                    'location'=>$item['location'],
                    'business' => $item['business'],
                    'caregiver'=>$item['caregiver'],
                    'system' => $item['system'],
                    'amount' => $item['amount'],
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
                $set[$key]['business'] += $item['business'];
                $set[$key]['caregiver'] += $item['caregiver'];
                $set[$key]['system'] += $item['system'];
            }
        }

        return array_values($set);
    }
}
