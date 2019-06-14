<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
//use \App\BusinessCommunications;
use Illuminate\Http\Request;

use Log;

class BusinessCommunicationsController extends Controller
{
    public function index(Request $request){

    }

    public function show($businessId){
      $comms  = \App\BusinessCommunications::where('business_id', $businessId)->first();
      return response()->json($comms);
    }

    public function create(Request $request, $businessId){

       $comms = \App\BusinessCommunications::where('business_id', $businessId)->first();

       if($comms){

           Log::info("UPDATE CLIENT");
           $comms->auto_off = $request->auto_off;
           $comms->on_indefinitely = ($request->auto_off == 'true') ? true : false;
           $comms->week_start = ($request->on_indefinitely == 'true') ? true : false;
           $comms->week_end = $request->week_end;
           $comms->weekend_start = $request->weekend_start;
           $comms->weekend_end = $request->weekend_end;
           $comms->message = $request->message;

       }else{
           Log::info("CREATE CLIENT");
           $comms = new \App\BusinessCommunications();
           $comms->auto_off = ($request->auto_off == 'true') ? true : false;
           $comms->on_indefinitely = ($request->on_indefinitely == 'true') ? true : false;
           $comms->week_start = $request->week_start;
           $comms->week_end = $request->week_end;
           $comms->weekend_start = $request->weekend_start;
           $comms->weekend_end = $request->weekend_end;
           $comms->message = $request->message;
           $comms->business_id = $businessId;
           $comms->save();
       }

       return response()->json($comms);

    }

    public function update(Request $request){

    }

}
