<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Requests\OccAccDeductiblesRequest;
use App\OccAccDeductible;
use App\Payments\SingleDepositProcessor;
use App\Reports\OccAccDeductiblesReport;
use App\Responses\CreatedResponse;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

            $data = $report->forWeekStartingAt( $request->start_date )->rows();

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

            $amount = multiply( $deductible[ 'amount' ], -1 );

            $caregiver = Caregiver::findOrFail( $deductible[ 'caregiver_id' ] );

            $invoice = SingleDepositProcessor::generateCaregiverAdjustmentInvoice( $caregiver, $amount, 'OccAcc' );

            // create the occAccDeductioon record
            $occAccDeductible = OccAccDeductible::create([

                'caregiver_id'         => $caregiver->id,
                'caregiver_invoice_id' => $invoice->id,
                'amount'               => $amount,
                'week_start'           => filter_date( $deductible[ 'start_date' ] ),
                'week_end'             => filter_date( $deductible[ 'end_date' ] ),
            ]);

            // DB::enableQueryLog();

            // run this shift query to associate all shifts to this deductible
            $shifts = Shift::forRequestedBusinesses([ $deductible[ 'businesses' ] ])
                ->whereConfirmed()
                ->whereHasntBeenUsedForOccAccDeductible()
                ->forCaregiver( $caregiver->id )
                ->whereBetween( 'checked_in_time',[

                    Carbon::parse( $deductible[ 'start_date' ] )->format( 'Y-m-d 00:00:00' ),
                    Carbon::parse( $deductible[ 'end_date' ] )->format( 'Y-m-d 23:59:59' )
                ])
                ->whereNotNull( 'checked_out_time' )
                ->get();

            // dd( DB::getQueryLog(), $shifts, Carbon::parse( $deductible[ 'start_date' ] )->format( 'Y-m-d 00:00:00' ), Carbon::parse( $deductible[ 'end_date' ] )->format( 'Y-m-d 23:59:59' ) );

            $occAccDeductible->shifts()->saveMany( $shifts );
        }

        \DB::commit();

        return new CreatedResponse( count( $data ) . " deductible invoices created" );
    }
}
