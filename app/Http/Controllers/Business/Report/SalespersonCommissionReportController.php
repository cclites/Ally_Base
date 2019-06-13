<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use App\Reports\SalespersonCommissionReport;
use Illuminate\Http\Request;

use App\Business;
use App\User;
use App\SalesPerson;
use App\BusinessChain;

use Illuminate\Support\Facades\Auth;
use Log;


class SalespersonCommissionReportController extends BaseController{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){

        return view_component('sales-people-commission-report', 'Salesperson Commission Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Populate the salesperson dropdown
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * generate the report
     *
     * @param Request $request
     * @param SalespersonCommissionReport $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request, SalespersonCommissionReport $report){

        if ($request->has('json')){
            // Validate the user has access to the requested business
            $businesses = Auth::user()->filterAttachedBusinesses([$request->business]);
            if (empty($businesses)) {
                // If empty, show all office locations they are assigned to
                $businesses = Auth::user()->getBusinessIds();
            }

            //this populates the report
            $data = $report->forBusinessId($businesses)
                ->forDates($request->dates['start'], $request->dates['end'])
                ->forSalespersonId($request->salesperson)
                ->rows();

            return response()->json($data);
        }

    }

}