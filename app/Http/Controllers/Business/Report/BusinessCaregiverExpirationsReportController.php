<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\ExpirationType;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\CertificationExpirationReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;

class BusinessCaregiverExpirationsReportController extends BaseController
{
    /**
     * Show the Caregiver Expirations Report.
     *
     * @param Request $request
     * @param CertificationExpirationReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, CertificationExpirationReport $report)
    {

        $expirationTypes = ExpirationType::where( 'chain_id', $this->businessChain()->id )
            ->orderBy( 'type' )
            ->get()
            ->values();

        if ($request->filled('json')) {

            $report->forRequestedBusinesses()
                ->setAllTypes( $expirationTypes )
                ->setCaregiver( $request->caregiver_id != 'scheduled' ? $request->caregiver_id : null )
                ->setActiveOnly($request->active === '1' ? true : false)
                ->setInactiveOnly($request->active === '0' ? true : false)
                ->setExpirationType($request->expiration_type)
                ->setExpired($request->show_expired == 1 ? true : false)
                ->setShowEmptyExpirations($request->show_empty_expirations === 'true' ? true : false)
                ->setBetweenDates($request->start_date, $request->end_date)
                ->setShowScheduled($request->show_scheduled === 'false' ? false : true );

            if ( $request->export === '1' ) {

                return $report->setDateFormat( 'm/d/Y g:i A', 'America/New_York' )
                    ->download();
            }

            if ( $request->deficiency_letter === '1' ) {

                $pages = $report->rows()->groupBy( 'caregiver_id' );

                foreach( $pages as $caregiverId => $deficiencyLetter ){

                    $deficiencyLetter->caregiver = Caregiver::with( 'address' )->find( $caregiverId );
                }

                return PDF::loadView( 'business.caregivers.deficiency_letters', [

                    'pages'       => $pages,
                    'intro'       => $request->intro_paragraph,
                    'middle'      => $request->middle_paragraph,
                    'outro'       => $request->outro_paragraph,
                    'final_words' => $request->final_words,
                    'farewell'    => $request->farewell,
                    'start_date'  => $request->start_date,
                    'end_date'    => $request->end_date,
                    'today'       => Carbon::now()->format( 'm/d/Y' )
                ])->inline( 'deficiency_letters.pdf' );
            }

            return response()->json( $report->rows() );
        }

        $caregivers = $this->businessChain()->caregivers()
            ->active()
            ->get()
            ->map(function ($item) {
                return ['id' => $item->id, 'name' => $item->nameLastFirst];
            })
            ->sortBy('name')
            ->values();

        return view_component(
            'business-caregiver-expirations-report',
            'Caregiver Expirations Report',
            compact( 'caregivers', 'expirationTypes' ),
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }
}
