<?php

namespace App\Http\Controllers\Business;

use App\User;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class PaginatedCaregiverController extends BaseController
{
    /**
     * Get a list of caregivers using pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perpage', 20);
        $page = $request->input('page', 1);
        $sortBy = $request->input('sort', 'lastname');
        $sortOrder = $request->input('desc', false) == 'true' ? 'desc' : 'asc';
        $offset = ($page - 1) * $perPage;
        $search = $request->input('search', null);

        $query = User::with('caregiver',
            'caregiver.businesses',
            'caregiver.address',
            'caregiver.phoneNumber'
        )
            ->where('role_type', 'caregiver')
            ->forRequestedBusinesses();

        // Need to join the address table to allow sorting by city/zip.
        $query->leftJoin('addresses', function ($join) {
            $join->on('users.id', '=', 'addresses.user_id')
                ->where('addresses.type', 'home');
        });

        // Being explicit with the selected fields allows easy
        // access to the city & zip fields but is also required
        // for the caregiver relation to load properly.
        $query->select('users.id as id', 'users.firstname as firstname', 'users.lastname as lastname', 'users.email as email', 'addresses.city as city', 'addresses.zip as zipcode');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.email', 'LIKE', "%$search%")
                    ->orWhere('users.id', 'LIKE', "%$search%")
                    ->orWhere('users.firstname', 'LIKE', "%$search%")
                    ->orWhere('users.lastname', 'LIKE', "%$search%");
            });

        }

        // Default to active only, unless active is provided in the query string
        if ($request->input('active', 1) !== null) {
            $query->where('active', $request->input('active', 1));
        }

        if ($request->input('status') !== null) {
            $query->where('status_alias_id', $request->input('status', null));
        }

        $total = $query->count();

        if ($sortBy == 'lastname' || !$sortBy) {
            $query->orderByRaw("users.lastname $sortOrder, users.firstname $sortOrder");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $results = $query->offset($offset)
            ->limit($perPage)
            ->get();

        return response()->json(compact('total', 'results'));
    }
}