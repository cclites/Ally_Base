<?php


namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Http\Controllers\Business\BaseController;
use App\Responses\ErrorResponse;
use App\Schedule;
use Illuminate\Http\Request;

class CaregiverAvailabilityConflictReport extends BaseController
{

    public function index(Request $request, Caregiver $caregiver){

        if(!$caregiver->id){
            new ErrorResponse('404', "Must select a Caregiver.");
        }

        $conflicts = \DB::table('caregiver_availability_conflict')
                        ->where('caregiver_id', $caregiver->id)
                        ->get();

        return view_component('caregiver-availability-conflict-report', 'Caregiver Availability Conflict Report', compact('caregiver', 'conflicts'), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}