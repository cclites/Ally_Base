<?php

namespace App\Http\Controllers\Admin\Report;

use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reports\PaidBilledAuditReport;
use App\SalesPerson;
use App\Business;

class PaidBilledAuditReportController extends Controller
{
    public function index(Request $request, PaidBilledAuditReport $report){

        if ($request->filled('json')) {

            if(!filled($request->business)){
                return new ErrorResponse(400,'You must first select a business location.');
            }

            $timezone = auth()->user()->role->getTimezone();

            //$this->authorize('read', Business::find($request->business));

            $data = $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->salesperson
                )->rows();

            //\Log::info("Returned some data");
           // \Log::info(json_encode($data->rows()));

            $totals = [
                'amount'=>$data->sum('amount'),
                'salesperson' =>filled($request->salesperson) ? SalesPerson::find($request->salesperson)->fullName() : 'All Salespeople',
                'location' => Business::find($request->business)->name(),
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

            if(!filled($item["salesperson"])){
                $item["salesperson"] = 'No Salesperson';
            }

            //\Log::info(json_encode($item));


            $key = $item["location"] . $item["salesperson"] . $item["date"] . $item['service'] . $item['caregiver'];

            if(!isset($set[$key])){
                $set[$key] = [
                    'location'=>$item['location'],
                    'client'=> $item['client_name'],
                    'caregiver'=>$item['caregiver'],
                    'service'=>$item["service"],
                    'hours' => $item['hours'],
                    'billable' =>$item['billable'],
                    'amount' => $item['amount'],
                    'salesperson' => $item['salesperson'],
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
                $set[$key]['hours'] += $item['hours'];
                $set[$key]['billable'] += $item['billable'];
            }

        }

        return array_values($set);


    }
}

/*
 * 'invoice_id' => $invoice->id,
            'invoice_name' => $invoice->name,
            'client_name' => $invoice->client->nameLastFirst,
            'caregiver' => optional($shiftService->shift->caregiver)->nameLastFirst,
            'hours' => $shiftService->duration,
            'service' => trim("{$shiftService->service->code} {$shiftService->service->name}"),
            'service_id' => $shiftService->service->id,
            'date' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateString(),
            'start' => Carbon::parse($shiftService->shift->checked_in_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'end' => Carbon::parse($shiftService->shift->checked_out_time->toDateTimeString(), $this->timezone)->toDateTimeString(),
            'billable' => m
 */
