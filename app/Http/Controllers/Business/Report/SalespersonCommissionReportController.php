<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use App\Reports\SalespersonCommissionReport;
use Illuminate\Http\Request;

use App\Business;
use App\User;
use App\SalesPerson;
use App\BusinessChain;

use Log;


class SalespersonCommissionReportController extends BaseController{


    public function index(Request $request){

        return view_component('sales-people-commission-report', 'Salesperson Commission Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function salesPeopleForCommissionReport(){

        $salesPersons = $this->businessChain()->salesPeopleForChain();

        $formatted = $salesPersons->map(function($person){
            return [
                'value'=> $person->id,
                'text'=>$person->fullName()
            ];
        });

        return response()->json($formatted);

    }

    public function generate(Request $request, SalespersonCommissionReport $report){

        if ($request->has('json')){

            //this populates the report
            $data = $report->forSalespersonId($request->salesperson)
                           ->rows();

            Log::info(json_encode($data));
            return response()->json($data);
        }



        //$dates = json_decode($request->dates);

        /*
        $request->validate([
            'dates->start' => 'required|date',
            'dates->end' => 'required|date',
            'business' => 'nullable|string',
            'salesperson' => 'nullable|string',
        ]);
        */

        //Log::info("Validated?");

        //$report->forDates($request->dates->start, $request->dates->end);
        //$report->forBusinessId($request->business);
        //$report->forSalerspersonId($request->salespersonId);

        //Log::info(json_encode($report->query()));
        //$data = $report->forDates($request->dates["start"], $request->dates["end"]);
                       //->forBusinessId($request->business)
                       //->forSalespersonId($request->salesperson)
                       //->get();

        //Log::info(json_encode($data));

        //return response()->json([]);
    }

}