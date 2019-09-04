<?php

namespace App\Http\Controllers\Business\Report;

use App\ExpirationType;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\CertificationExpirationReport;

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

            $results = $report->forRequestedBusinesses()
                ->setAllTypes( $expirationTypes )
                ->setCaregiver($request->caregiver_id)
                ->setActiveOnly($request->active === '1' ? true : false)
                ->setInactiveOnly($request->active === '0' ? true : false)
                ->setExpirationType($request->expiration_type)
                ->setExpired($request->show_expired == 1 ? true : false)
                ->setBetweenDates($request->start_date, $request->end_date)
                ->rows();

            // dd( $results );

            return response()->json( $results );
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
