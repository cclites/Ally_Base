<?php


namespace App\Http\Controllers\Business\Report;

use App\Billing\ClientPayer;
use App\Business;
use App\Client;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\PaymentSummaryByPayerReport;

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

            $data = $this->createSummary($data);

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

    /**
     * Condense the results
     *
     * @param $data
     * @return array
     */
    protected function createSummary($data): array
    {

        $set = [];

        foreach($data as $item){

            $key = $item['client_name'] . $item['payer'] . $item['date'] . $item['client_type'];

            if(!isset($set[$key])){

                $set[$key] = [
                    'payer'=>$item['payer'],
                    'client_name'=>$item['client_name'],
                    'date'=>$item['date'],
                    'client_type'=>$item['client_type'],
                    'amount'=>$item['amount']
                ];
            }else{
                $set[$key]['amount'] += $item['amount'];
            }

        }

        return array_values($set);
    }


}