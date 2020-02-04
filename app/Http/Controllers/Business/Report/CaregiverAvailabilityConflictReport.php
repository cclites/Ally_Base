<?php


namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Caregiver;
use App\Http\Controllers\Business\BaseController;
use App\Responses\ErrorResponse;
use App\Schedule;
use Illuminate\Http\Request;

class CaregiverAvailabilityConflictReport extends BaseController
{

    public function index(Request $request, Caregiver $caregiver = null){

        \Log::info($caregiver);

        if($caregiver){

            $businessId= $caregiver->businesses->first()->id;

            $conflicts = \DB::table('caregiver_availability_conflict')
                ->where('caregiver_id', $caregiver->id)
                ->where('business_id', $businessId)
                ->get();
        }elseif($request->businesses && $request->caregiver){
            $conflicts = \DB::table('caregiver_availability_conflict')
                ->where('caregiver_id', $request->caregiver)
                ->where('business_id', $request->businesses)
                ->get();
        }elseif($request->businesses){
            $conflicts = \DB::table('caregiver_availability_conflict')
                ->where('business_id', $request->businesses)
                ->get();
        }else{
            $conflicts = [];
        }

        if($request->json){
            return response()->json($conflicts);
        }


        return view_component('caregiver-availability-conflict-report', 'Caregiver Availability Conflict Report', compact('caregiver', 'conflicts'), [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}