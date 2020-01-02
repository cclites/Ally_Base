<?php

namespace App\Http\Controllers\Business;

use App\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class PaginatedClientController extends BaseController
{
    /**
     * Get a list of clients using pagination.
     * 
     * Also used by the Avery Label Table for displaying paginated Clients!! make sure to ensure that still works if you change this.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index( Request $request )
    {
        \Log::info("PaginatedClientController::index");

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
            if ($servicesCoordinatorId = $request->input('services_coordinator_id')) {
                $query->whereHas('servicesCoordinator', function ($q) use ($servicesCoordinatorId) {
                    $q->where('id', $servicesCoordinatorId);
                });
            }
            // Use query string ?address=1&phone_number=1&care_plans=1&services_coordinators=1 if data is needed
            if ($request->input('address')) {
                $query->with('address');
            }
            if ($request->input('phone_number')) {
                $query->with('phoneNumber');
            }
            if ($request->input('care_plans')) {
                $query->with('carePlans');
            }
            if ($request->input('services_coordinators')) {
                $query->with('servicesCoordinator');
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


            $daysSinceShift = $request->input( 'daysPassed', null );
            if ( filled($daysSinceShift) ) {

                $now = Carbon::now();
                $daysAgo = Carbon::now()->subdays( $daysSinceShift );

                $query->whereHas( 'shifts', function( $q ) use( $now, $daysAgo ){

                    $q->whereBetween( 'checked_in_time', [ $daysAgo, $now ] );
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