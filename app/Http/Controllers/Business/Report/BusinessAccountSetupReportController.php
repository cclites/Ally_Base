<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use App\Reports\BusinessClaimsArAgingReport;
use App\Reports\CaregiverAccountSetupReport;
use Illuminate\Http\Request;
use App\Reports\ClientAccountSetupReport;

class BusinessAccountSetupReportController extends Controller
{
    /**
     * Get the Payroll Export Report for caregivers
     *
     * @param Request $request
     * @param CaregiverAccountSetupReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request, CaregiverAccountSetupReport $report)
    {
        if ($request->filled('json')) {
            $report->query()->forRequestedBusinesses();

            $report->setStatusFilter($request->status)
                ->setPhoneFilter($request->phone);

            if ($request->input('export')) {
                return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                    ->download();
            }

            return response()->json($report->rows());
        }

        return view_component('business-account-setup-report', 'Caregiver Account Setup Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Get the Payroll Export Report for clients
     *
     * @param Request $request
     * @param ClientAccountSetupReport $report
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function client( Request $request, ClientAccountSetupReport $report )
    {
        if ( $request->filled( 'json' ) ) {

            $report->query()->forRequestedBusinesses();

            $report->setStatusFilter( $request->status )
                ->setPhoneFilter( $request->phone );

            if ( $request->input( 'export' ) ) {

                return $report->setDateFormat( 'm/d/Y g:i A', 'America/New_York' )
                    ->download();
            }

            return response()->json( $report->rows() );
        }

        return view_component( 'business-client-account-setup-report', 'Client Account Setup Report', [], [

            'Home'    => route( 'home' ),
            'Reports' => route( 'business.reports.index' )
        ]);
    }
}