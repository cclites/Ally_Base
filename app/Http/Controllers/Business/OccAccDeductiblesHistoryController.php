<?php

namespace App\Http\Controllers\Business;

use App\Reports\OccAccDeductiblesHistoryReport;
use Illuminate\Http\Request;

class OccAccDeductiblesHistoryController extends BaseController
{

    /**
     * Gather a report for all Caregivers who receive OccAcc deductibles
     * @param Request $request
     *
     * @param OccAccDeductiblesHistoryReport $report
     * @return array
     */
    public function index( Request $request, OccAccDeductiblesHistoryReport $report )
    {
        if( $request->filled( 'json' ) ){

            $data = $report->betweenDates( $request->start_date, $request->end_date )->rows();

            if( $request->has( 'export' ) && $request->export == 1 ) {

                return $report->setDateFormat( 'm/d/Y g:i A', auth()->user()->getTimezone() )
                    ->download();
            }

            return response()->json( $data );
        }

        return view_component( 'occ-acc-deductibles-history', 'OccAcc Deductibles History Report', [], [

            'Home'    => route( 'home' ),
            'Reports' => route( 'business.reports.index' )
        ]);
    }
}
