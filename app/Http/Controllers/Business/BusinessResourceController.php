<?php

namespace App\Http\Controllers\Business;

use Illuminate\Support\Collection;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessResourceController extends BaseController
{
    /**
     * Fetch a single resource based on the url path resource parameter.
     *
     * @param Request $request
     * @param string $resource
     * @return ErrorResponse
     */
    public function index(Request $request, string $resource)
    {
        return response()->json(
            $this->getResourceData($resource)
        );
    }

    /**
     * Fetch multiple resources in one request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function multi(Request $request)
    {
        $data = [];

        foreach (explode(',', $request->resources) as $resource) {
            $data[$resource] = $this->getResourceData($resource)->toArray();
        }

        return response()->json($data);
    }

    /**
     * Call the corresponding resource method using reflection.
     *
     * @param string $resource
     * @return Collection
     */
    protected function getResourceData(string $resource) : Collection
    {
        $method = Str::camel(trim($resource)).'Resource';

        if (! method_exists($this, $method)) {
            return collect([]);
        }

        return $this->$method(request());
    }

    /**
     * Return a list of clients.
     *
     * @param Request $request
     * @return Collection
     */
    public function clientsResource(Request $request) : Collection
    {
        $clients = \DB::table('clients')
            ->join('users', 'clients.id', '=', 'users.id')
            ->select('clients.id', \DB::raw("CONCAT(COALESCE(lastname, ''), ', ', COALESCE(firstname, '')) as name"), 'business_id', 'client_type', 'active')
            ->whereIn('clients.business_id', $this->authorizedBusinessIds())
            ->orderBy('name')
            ->get();

        return $clients->map(function ($item) {
                return (array) $item;
            })
            ->values();
    }

    /**
     * Return a list of caregivers.
     *
     * @param Request $request
     * @return Collection
     */
    public function caregiversResource(Request $request) : Collection
    {
        $caregivers = \DB::table('caregivers')
            ->leftJoin('users', 'caregivers.id', '=', 'users.id')
            ->leftJoin('business_caregivers', 'caregivers.id', '=', 'business_caregivers.caregiver_id')
            ->whereIn('business_caregivers.business_id', $this->authorizedBusinessIds())
            ->whereNotNull('users.id')
            ->select('caregivers.id', \DB::raw("CONCAT(COALESCE(lastname, ''), ', ', COALESCE(firstname, '')) AS name"), 'business_id', 'active')
            ->orderBy('name')
            ->get();

        return $caregivers->groupBy('id')
            ->map(function ($group) {
                $data = (array) $group->first();
                return array_merge(
                    array_except($data, 'business_id'),
                    ['businesses' => $group->map(function ($item) {
                        return $item->business_id;
                    })]
                );
            })
            ->values();
    }

    /**
     * Return a list of services.
     *
     * @param Request $request
     * @return Collection
     */
    public function servicesResource(Request $request) : Collection
    {
        $services = \DB::table('services')
            ->select('id', 'name', 'code', 'default')
            ->where('chain_id', $this->businessChain()->id)
            ->orderBy('name')
            ->get();

        return $services->map(function ($item) {
                return (array) $item;
            })
            ->values();
    }
}
