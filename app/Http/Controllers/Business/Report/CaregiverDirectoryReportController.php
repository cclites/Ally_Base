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

            if ( $request->export == '1' ) {

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

    /**
     * Handle the request to generate the caregiver directory
     * 
     * I feel safe manipulating this because i did a global check and didnt find anything using this or the route that accesses this
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function generateCaregiverDirectoryReport( Request $request )
    {
        $report = new CaregiverDirectoryReport();
        $report->forRequestedBusinesses();

        switch( $request->active ){

            case 'true':

                $query->active();
                break;
            case 'false':

                $query->inactive();
                break;
            default:
                break;
        }

        // $report->applyColumnFilters($request->except(['filter_start_date','filter_end_date','filter_active']));

        return $report->rows();
    }
}