<?php

namespace App\Http\Controllers\Business;

use App\Responses\SuccessResponse;
use App\Client;
use App\Caregiver;
use DB;

class QuickSearchController extends BaseController
{
    public function index()
    {
        if (!request()->has('q')) {
            return new SuccessResponse(null, []);
        }

        $q = request('q');
        
        if (empty($this->business()->id)) {
            // admin -> show all
            $clients = Client::with('user');
            $caregivers = Caregiver::with('user');
        } else {
            // only show for current business
            $clients = $this->business()->clients()->with('user');
            $caregivers = $this->business()->caregivers()->with('user');
        }

        $nameQuery = "CONCAT(firstname, ' ', lastname) like '%$q%'";

        // check if testing enviornment because sqlite doesn't have CONCAT function
        if (\App::runningUnitTests()) {
            $nameQuery = "printf('%s %s', firstname, lastname) like '%$q%'";
        }

        $clients = $clients->whereHas('user', function ($query) use($q, $nameQuery) {
                $query->where('active', true)
                    ->whereRaw($nameQuery);
            })
            ->get();

        $caregivers = $caregivers->whereHas('user', function ($query) use($q, $nameQuery) {
                $query->where('active', true)
                    ->whereRaw($nameQuery);
            })
            ->get();

        $data = $clients->concat($caregivers)
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->user->firstname . ' ' . $item->user->lastname,
                    'role_type' => $item->user->role_type,
                ];
            })->sortBy('name')->values()->all();

        return new SuccessResponse(null, $data);
    }
}
