<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Caregiver;
use App\Client;
use App\Schedule;

class AuditLogController extends Controller
{
    public function show(Request $request){

        $scheduleId = $request->schedule_id;
        $clientId = $request->client_id;
        $caregiverId = $request->caregiver_id;

        if(filled($scheduleId)){
            return response()->json(Schedule::find($scheduleId)->auditTrail());
        }

        if(filled($clientId)){
            return response()->json(Client::find($clientId)->auditTrail());
        }

        if(filled($caregiverId)){
            return response()->json(Caregiver::find($caregiverId)->auditTrail());
        }
    }
}
