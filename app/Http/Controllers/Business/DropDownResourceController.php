<?php

namespace App\Http\Controllers\Business;

use App\Billing\Payer;
use App\Billing\Service;
use App\Caregiver;
use App\Client;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Http\Resources\SalespersonDropdownResource;
use App\Http\Resources\ServicesDropdownResource;
use App\Http\Resources\VisitEditActionResource;
use App\Http\Resources\VisitEditReasonResource;
use App\Responses\ErrorResponse;
use App\SalesPerson;
use App\VisitEditAction;
use App\VisitEditReason;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DropdownResourceController extends BaseController
{
    /**
     * A list of the resource types that may be called through the route.
     * @var array
     */
    const AVAILABLE_RESOURCES = [
        'clients',
        'caregivers',
        'payers',
        'sales-people',
        'marketing-clients',
        'clients-for-chain',
        'services',
        'visit-edit-codes',
        'cities',
    ];

    /**
     * Determine the type of resource the request is looking
     * for and call the related local function.
     *
     * @param Request $request
     * @param string $resource
     * @return ErrorResponse
     */
    public function index(Request $request, string $resource)
    {
        $method = Str::camel($resource);

        if (!in_array($resource, self::AVAILABLE_RESOURCES) || !method_exists($this, $method)) {
            return new ErrorResponse(500, 'That resource does not exist.');
        }

        return $this->$method($request);
    }

    protected function clients(Request $request)
    {
        $query = Client::forRequestedBusinesses();

        if ($request->inactive != 1) {
            $query->active();
        }

        if ($request->filled('client_type')) {
            $query->whereClientType($request->client_type);
        }

        if ($request->filled('payer_id')) {
            $query->whereHas('payers', function ($q) use ($request) {
                $q->where('payer_id', $request->payer_id);
            });
        }

        $clients = $query->get();
        return response()->json(new ClientDropdownResource($clients));
    }

    protected function caregivers(Request $request)
    {
        $query = Caregiver::forBusinesses([$request->business]);

        if ($request->active == 'all') {
            // no active scope
        } else if ($request->active == '0') {
            $query->inactive();
        } else {
            $query->active();
        }

        $caregivers = new CaregiverDropdownResource($query->get());
        return response()->json($caregivers);
    }

    protected function payers(Request $request)
    {
        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->get());
        return response()->json($payers);
    }

    protected function salesPeople(Request $request)
    {
        $salesPeople = SalesPerson::forRequestedBusinesses()->get();
        return response()->json(new SalespersonDropdownResource($salesPeople));
    }

    protected function marketingClients(Request $request)
    {
        $clients = Client::forRequestedBusinesses()
            ->active()
            ->whereNotNull('sales_person_id')
            ->get();

        return response()->json(new ClientDropdownResource($clients));
    }

    protected function clientsForChain(Request $request)
    {
        $query = Client::forChain( $request->chain );

        if( $request->active == 'active' ) {

            $query->active();
        } else if ( $request->active == 'inactive' ) {

            $query->inactive();
        } // else just do all

        $clients = $query->get();

        return response()->json( new ClientDropdownResource( $clients ) );
    }

    public function services(Request $request)
    {
        $services = new ServicesDropdownResource(Service::forAuthorizedChain()->get());
        return response()->json($services);
    }

    public function visitEditCodes(Request $request)
    {
        $visitEditReasons = new VisitEditReasonResource( VisitEditReason::all() );
        $visitEditActions = new VisitEditActionResource( VisitEditAction::all() );
        return response()->json([ 'reasons' => $visitEditReasons, 'actions' => $visitEditActions ]);
    }

    public function cities(Request $request){

        $cities = [];

        $cities = Client::forRequestedBusinesses()
                    ->with(['addresses'])
                    ->get()
                    ->map(function($client) use ($cities){

                        $city = $client->addresses->first()["city"];

                        if(!in_array($city, $cities)){
                            return $city;
                        }
                        return;

                    })->filter()
                    ->toArray();

        $cities = array_unique($cities);
        sort($cities, SORT_STRING);

        return response()->json($cities);
    }
}
