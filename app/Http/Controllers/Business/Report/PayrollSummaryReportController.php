<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Reports\PayrollSummaryReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Business\BaseController;
use App\Http\Resources\CaregiverDropdownResource;
use App\Caregiver;
use Illuminate\Http\Response;

/**
 *
 * @package App\Http\Controllers\Business\Report
 */
class PayrollSummaryReportController extends BaseController
{
    /**
     * @param Request
     * @param PayrollSummaryReport
     */
    public function index(Request $request, PayrollSummaryReport $report ){

        if ($request->filled('json') || $request->filled('print')) {

           $timezone = auth()->user()->role->getTimezone();

            $data = $report->setTimezone($timezone)
                    ->applyFilters(
                        $request->start,
                        $request->end,
                        $request->business,
                        $request->client_type,
                        $request->caregiver
                    )->rows();

            $clientType = $request->client_type ? ucfirst(str_replace("_", " ", $request->client_type)) : "All Clients";
            $businessName = Business::find($request->business)->name;
            $caregiverName = $request->caregiver ? Caregiver::find($request->caregiver)->nameLastFirst() : 'All Caregivers';

            $totals = [
                'amount'=>$data->sum('amount'),
                'location' => $businessName,
                'caregiver' => $caregiverName,
                'type' => $clientType,
                'start' => $request->start,
                'end' => $request->end
            ];

            $data = $this->createSummary($data);

            if($request->filled('print')){
                return $this->printReport($data, $totals);
            }


            return response()->json(['data'=>$data, 'totals'=>$totals]);
        }


        return view_component('payroll-summary-report', 'Payroll Summary Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function createSummary($items){

        $set = [];

        foreach($items as $item){

            $key = $item['caregiver'];

            if(!isset($set[$key])){
                $set[$key]['caregiver'] = $item['caregiver'];
                $set[$key]['deposits'][] = $item['deposits'];
                $set[$key]['amount'] = $item['amount'];
            }else{
                if($this->isUnique($set[$key]['deposits'], $item['deposits'] )){
                    $set[$key]['deposits'][] = $item['deposits'];
                }
                $set[$key]['amount'] += $item['amount'];
            }
        }

        $data = [];

        foreach($set as $key=>$value){
            $data[] = $value;
        }

        return $data;
    }

    public function isUnique($set, $item){

        foreach($set as $s){

            if($s['created_at'] == $item['created_at']){
                return false;
            }
        }

        return true;
    }

    public function caregivers($businessId){
        $caregivers = new CaregiverDropdownResource(Caregiver::forBusinesses([$businessId])->active()->get());
        return response()->json($caregivers);
    }

    /**
     * Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReport($data, $totals) : \Illuminate\Http\Response
    {
        $html = response(view('business.reports.print.payroll_summary',['data'=>$data, 'totals'=>$totals]))->getContent();

        $snappy = \App::make('snappy.pdf');
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="payroll_summary.pdf"'
            )
        );
    }

}
