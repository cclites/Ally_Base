<?php

namespace App\Http\Controllers\Business;

use App\Client;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class PaginatedClientController extends BaseController
{
    /**
     * Get a list of clients using pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index( Request $request )
    {
        if ( $request->filled( 'json' ) || $request->expectsJson() ) {

            $query = Client::forRequestedBusinesses();

            // sorting controls using the BaseModel class
            $this->orderedColumn = $request->input( 'sort', 'users.lastname' ); // Erik TODO => this may need adjustments
            $order = $request->input( 'sortDirection', 'asc' );
            $query->ordered( $order );

            // Default to active only, unless active is provided in the query string
            if ($request->input('active', 1) !== null) {
                $query->where('active', $request->input('active', 1));
            }
            if ($request->input('status') !== null) {
                $query->where('status_alias_id', $request->input('status', null));
            }
            if ($clientType = $request->input('client_type')) {
                $query->where('client_type', $clientType);
            }
            if ($caseManagerId = $request->input('case_manager_id')) {
                $query->whereHas('caseManager', function ($q) use ($caseManagerId) {
                    $q->where('id', $caseManagerId);
                });
            }
            // Use query string ?address=1&phone_number=1&care_plans=1&case_managers=1 if data is needed
            if ($request->input('address')) {
                $query->with('address');
            }
            if ($request->input('phone_number')) {
                $query->with('phoneNumber');
            }
            if ($request->input('care_plans')) {
                $query->with('carePlans');
            }
            if ($request->input('case_managers')) {
                $query->with('caseManager');
            }

            $search = $request->input( 'search', null );

            if ( $search ) {

                $query->where( function ($q) use ( $search ) {

                    $q->where( 'users.email', 'LIKE', "%$search%" )
                        ->orWhere( 'users.id', 'LIKE', "%$search%" )
                        ->orWhere( 'users.firstname', 'LIKE', "%$search%" )
                        ->orWhere( 'users.lastname', 'LIKE', "%$search%" );
                });
            }

            // grab total before pagination
            $total = $query->count();

            $per_page     = $request->input( 'perPage', 50 );
            $current_page = $request->input( 'page', 1 );
            $query->limit( $per_page )->offset( $per_page * ( $current_page - 1 ) );

            $clients = $query->get();

            $data = [

                'total'   => $total,
                'clients' => $clients
            ];

            return $data;
        }
    }
}