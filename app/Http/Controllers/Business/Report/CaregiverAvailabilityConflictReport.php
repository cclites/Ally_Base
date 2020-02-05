<?php


namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Caregiver;
use App\CaregiverAvailabilityConflict;
use App\Http\Controllers\Business\BaseController;
use App\Responses\ErrorResponse;
use App\Schedule;
use Illuminate\Http\Request;

class CaregiverAvailabilityConflictReport extends BaseController
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request){



        if($request->filled('json')){

            $query = CaregiverAvailabilityConflict::with(['caregiver'])
                            ->where('business_id', $request->business);

            if($request->filled('caregiver')){
                $query->where('caregiver_id', $request->caregiver);
            }

            $conflicts = $this->formatReport($query->get());

            return response()->json($conflicts);

        }

        return view_component('caregiver-availability-conflict-report', 'Caregiver Availability Conflict Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);


    }

    /**
     * Shows the report coming from the office user link in Caregiver Availability
     * Conflict modal.
     *
     * @param Caregiver $caregiver
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Caregiver $caregiver){

        $businessId= $caregiver->businesses->first()->id;

        $conflicts = CaregiverAvailabilityConflict::with(['caregiver'])
                        ->where('caregiver_id', $caregiver->id)
                        ->where('business_id', $businessId)
                        ->get();

        $conflicts = $this->formatReport($conflicts);

        return view_component('caregiver-availability-conflict-report', 'Caregiver Availability Conflict Report', compact('caregiver', 'conflicts'),       [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    public function formatReport($conflicts){

        return $conflicts->map(function($conflict){
            return [
                'schedule_id' => $conflict->schedule_id,
                'starts_at' => $conflict->starts_at,
                'reason' => $conflict->reason,
                'caregiver_name' => $conflict->caregiver->name,
                'caregiver_id' => $conflict->caregiver->id,
                'caregiver_email' => $conflict->caregiver->email
            ];
        });

    }
}