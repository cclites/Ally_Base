<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Requests\OccAccDeductiblesRequest;
use App\Payments\SingleDepositProcessor;
use App\Reports\OccAccDeductiblesReport;
use App\Responses\CreatedResponse;
use Illuminate\Http\Request;

class OccAccDeductiblesController extends BaseController
{

    /**
     * Gather a report for all Caregivers who receive OccAcc deductibles
     * @param Request $request
     *
     * @return array
     */
    public function index( Request $request, OccAccDeductiblesReport $report )
    {
        if( $request->expectsJson() ){

            $data = $report->forTheFollowingBusinesses( $request->businesses )
                ->forWeekEndingAt( $request->end_date )
                ->rows();

            if ($request->has('export')) {
                return $report->setDateFormat('m/d/Y g:i A', $this->business()->timezone)
                    ->download();
            }

            return response()->json( $data );
        }

        return view_component( 'occ-acc-deductibles-report', 'OccAcc Deductibles Report', [], [

            'Home'    => route( 'home' ),
            'Reports' => route( 'business.reports.index' )
        ]);
    }

    /**
     * Store a new CaregiverRestriction
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store( OccAccDeductiblesRequest $request )
    {
        $data = $request->validated();

        \DB::beginTransaction();

        foreach( $data as $deductible ) {

            $amount = (float) $deductible[ 'amount' ] * -1;

            $caregiver = Caregiver::findOrFail( $deductible[ 'caregiver_id' ] );

            SingleDepositProcessor::generateCaregiverAdjustmentInvoice( $caregiver, $amount, 'OccAcc' );
        }

        \DB::commit();

        return new CreatedResponse( count( $data ) . " deductible invoices created" );
    }
}
