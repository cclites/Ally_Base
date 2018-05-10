<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateManualTimesheetsRequest;

class ManualTimesheetsController extends Controller
{
    /**
     * @return \App\Caregiver
     */
    protected function caregiver()
    {
        return auth()->user()->role;
    }

    /**
     * Returns form for creating a manual shift.
     *
     * @return void
     */
    public function create()
    {
        $caregiver = '{}';
            if (auth()->user()->role_type == 'caregiver') {
            $caregiver = auth()->user();
        }

        $business = activeBusiness();
        $activities = $business->allActivities();
        $caregivers = $this->caregiverClientList($business);

        return view('caregivers.manual_timesheets', compact(
            'caregiver',
            'caregivers', 
            'activities'
        ));
    }

    /**
     * Handles submission of manual shifts.
     *
     * @return void
     */
    public function store(CreateManualTimesheetsRequest $request)
    {
        dd($request->validated());
        // dd(request()->all());
    }

    /**
     * Gets list of all the businesses caregivers with attached clients
     * in simple array.  Intended for smart dropdowns.
     *
     * @return Array
     */
    public function caregiverClientList($business)
    {
        return $business->caregivers()->with('clients')->get()->map(function ($cg) {
            return [
                'id' => $cg->id,
                'name' => $cg->nameLastFirst,
                'clients' => $cg->clients->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->nameLastFirst,
                        'caregiver_hourly_rate' => $c->pivot->caregiver_hourly_rate,
                        'provider_hourly_fee' => $c->pivot->provider_hourly_fee,
                    ];
                }),
            ];
        });
    }
}
