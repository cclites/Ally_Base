<?php


namespace App\Http\Controllers\Business\Report;


use App\Caregiver;
use App\CustomField;
use App\Http\Controllers\Business\BaseController;
use App\Reports\CaregiverDirectoryReport;
use App\Responses\ErrorResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CaregiverDirectoryReportController extends BaseController
{
    /**
     * Shows the page to generate the caregiver directory
     *
     * @return Response
     */
    public function index( Request $request )
    {
        if( $request->filled( 'json' ) ){

            $report = new CaregiverDirectoryReport();
            $report->query()->forRequestedBusinesses();
            $report->setActiveFilter( $request->active );
            $report->setDateFilter( $request->start_date, $request->end_date );

            if ( $request->export == '1' ) {
                // the request object attributes are coming through as strings

                return $report->setDateFormat( 'm/d/Y g:i A', 'America/New_York' )
                    ->download();
            }

            return response()->json( $report->rows() );
        }

        $fields = CustomField::forAuthorizedChain()
            ->where( 'user_type', 'caregiver' )
            ->with( 'options' )
            ->get();

        return view( 'business.reports.caregiver_directory', compact( 'fields' ) );
    }
}