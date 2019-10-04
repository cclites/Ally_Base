<?php


namespace App\Http\Controllers\Business\Report;

use App\Billing\ClientPayer;
use App\Business;
use App\Billing\Payments\PaymentMethodType;
use App\Client;
use App\ClientType;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\PaymentSummary;
use Illuminate\Http\Response;

class PaymentSummaryReportController extends BaseController
{
    public function index(Request $request, PaymentSummary $report){

        if ($request->filled('json') || $request->filled('print')) {

            $timezone = auth()->user()->role->getTimezone();
            $this->authorize('read', Business::find($request->business));

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client_type,
                    $request->client,
                    $request->payment_method
                );

            $data = $report->rows();
            $location = Business::find($request->business)->name;
            $clientName = $request->client ? Client::find($request->client)->nameLastFirst : 'All Clients';

            $totals = [
                'location'=>$location,
                'client_name'=>$clientName,
                'total'=>$data->sum('amount'),
            ];

            $data = $this->createSummary($data);

            if ($request->filled('print')) {
                return $this->printReport($data, $totals);
            }

            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }

        $cTypes = ClientType::all();
        $clienttypes = [];
        foreach($cTypes as $key=>$val){
            $clienttypes[] = (object)['id'=>$key, 'name'=>ucwords(str_replace("_", " ", $val))];
        }

        $pTypes = PaymentMethodType::all();
        $paymenttypes = [];
        foreach($pTypes as $key=>$val){
            $paymenttypes[] = (object)['id'=>$key, 'name'=>ucwords(str_replace("_", " ", $val))];
        }

        return view_component(
            'payment-summary-by-payer',
            'Payment Summary by Payment Method',
             compact('clienttypes', 'paymenttypes'),
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

            $key = $item['client_name'] . $item['invoice'] . $item['date'];

            if(!isset($set[$key])){
                $set[$key] = [
                    'client_name'=>$item['client_name'],
                    'date'=>$item['date'],
                    'invoice'=>$item['invoice'],
                    'amount'=>$item['amount'],
                    'client_type'=>$item['client_type'],
                    'caregiver_amount'=>$item['caregiver_amount'],
                    'registry_amount'=>$item['registry_amount'],
                    'payment_type'=>$item['payment_type']
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
        $html = response(view('business.reports.print.payment_summary_by_private_payer',['data'=>$data, 'totals'=>$totals]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="payment_summary_by_private_payer.pdf"'
            )
        );
    }


}