<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Requests\OccAccDeductiblesReportRequest;
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
     * @param OccAccDeductiblesReport $report
     * @return array
     */
    public function index( Request $request, OccAccDeductiblesReport $report )
    {
        if( $request->expectsJson() ){

            $data = $report->forWeekStartingAt( $request->start_date )->rows();

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
     * @param OccAccDeductiblesReportRequest $request
     * @return CreatedResponse
     */
    public function store( OccAccDeductiblesReportRequest $request )
    {
        $data = $request->validated();
        $deduction = config( 'ally.occ_acc_deductible' );

        DB::beginTransaction();

        foreach( $data as $deductible ) {
            $caregiver = Caregiver::findOrFail( $deductible[ 'caregiver_id' ] );
            $totalDeduction = (float) 0.00;

            // run this shift query to associate all shifts to this deductible
            $shifts = Shift::forRequestedBusinesses( $deductible[ 'businesses' ] ) // this is actually necessary since it is nested, I tested it
                ->whereConfirmed()
                ->whereHasntBeenUsedForOccAccDeductible()
                ->forCaregiver( $caregiver->id )
                ->whereBetween( 'checked_in_time',[

                    Carbon::parse( $deductible[ 'start_date' ] )->format( 'Y-m-d 00:00:00' ),
                    Carbon::parse( $deductible[ 'end_date' ] )->format( 'Y-m-d 23:59:59' )
                ])
                ->whereNotNull( 'checked_out_time' )
                ->get()
                ->map( function ( Shift $shift ) use ( $deduction, &$totalDeduction ){

                    $duration = $shift->duration();
                    $amount = min( 9.00, multiply( $duration, $deduction ) );
                    $totalDeduction = min( 9.00, add( $totalDeduction, $amount ) );
                    return [
                        'shift_id' => $shift->id,
                        'duration' => $duration,
                        'amount' => multiply($amount, -1),
                    ];
                });

            $totalDeduction = multiply( $totalDeduction, -1 );

            $invoice = SingleDepositProcessor::generateCaregiverAdjustmentInvoice( $caregiver, $totalDeduction, 'OccAcc' );

            $occAccDeductible = OccAccDeductible::create([

                'caregiver_id'         => $caregiver->id,
                'caregiver_invoice_id' => $invoice->id,
                'amount'               => $totalDeduction,
                'week_start'           => filter_date( $deductible[ 'start_date' ] ),
                'week_end'             => filter_date( $deductible[ 'end_date' ] ),
            ]);

            $occAccDeductible->shifts()->createMany($shifts);
        }

        DB::commit();

        return new CreatedResponse( count( $data ) . " deductible invoices created." );
    }
}
