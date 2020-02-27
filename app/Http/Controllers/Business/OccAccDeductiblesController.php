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

            $amount = multiply( $deductible[ 'amount' ], -1 );

            $caregiver = Caregiver::findOrFail( $deductible[ 'caregiver_id' ] );

            $invoice = SingleDepositProcessor::generateCaregiverAdjustmentInvoice( $caregiver, $amount, 'OccAcc' );

            // create the occAccDeductioon record
            $occAccDeductible = OccAccDeductible::create([

                'caregiver_id'         => $caregiver->id,
                'caregiver_invoice_id' => $invoice->id,
                'amount'               => $amount,
                'week_start'           => $deductible[ 'start_date' ],
                'week_end'             => $deductible[ 'end_date' ],
            ]);

            // run this shift query to associate all shifts to this caregiver

            $shifts = Shift::whereBetween( 'checked_in_time',[

                Carbon::parse( $deductible[ 'start_date' ] )->startOfDay(),
                Carbon::parse( $deductible[ 'end_date' ] )->endOfDay()
            ])
            ->whereNotNull( 'checked_out_time' )
            ->where( 'caregiver_id', $caregiver->id )
            ->get();

            $occAccDeductible->shifts()->attach( $shifts );
        }

        \DB::commit();

        return new CreatedResponse( count( $data ) . " deductible invoices created" );
    }
}
