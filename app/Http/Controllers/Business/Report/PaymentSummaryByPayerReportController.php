<?php


namespace App\Http\Controllers\Business\Report;

use App\Billing\ClientPayer;
use App\Business;
use App\Client;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\PaymentSummaryByPayerReport;

use Log;

class PaymentSummaryByPayerReportController extends BaseController
{
    public function index(Request $request, PaymentSummaryByPayerReport $report){


        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client_type,
                    $request->client,
                    $request->payer
                );

            $data = $report->rows();

            $clientType = $request->client_type ? ucfirst(str_replace("_", " ", $request->client_type)) : "All Client Types";
            $location = Business::find($request->business)->name;
            $clientName = $request->client ? Client::find($request->client)->nameLastFirst : 'All Clients';
            $payerName = $request->payer ? ClientPayer::find($request->payer)->payer->name : 'All Payers';

            $totals = [
                'location'=>$location,
                'client_type'=>$clientType,
                'client_name'=>$clientName,
                'payer'=>$payerName,
                'total'=>$data->sum('amount')
            ];

            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        return view_component(
            'payment-summary-by-payer',
            'Payment Summary By Payer Report',
             [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }


}