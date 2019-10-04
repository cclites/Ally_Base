<?php

namespace App\Http\Controllers\Business;

use App\Http\Resources\AuditLogResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Caregiver;
use App\Client;
use App\Schedule;

class AuditLogController extends Controller
{
    public function show(Request $request){

        // Note: when adding new models to this controller make sure to
        // identify and clear any sensitive and/or encrypted data
        // at the AuditLogResource level before returning to the front-end.

        $scheduleId = $request->schedule_id;
        $clientId = $request->client_id;
        $caregiverId = $request->caregiver_id;

        if(filled($scheduleId)){
            return response()->json(
                AuditLogResource::collection(Schedule::find($scheduleId)->auditTrail())
            );
        }

        if(filled($clientId)){
            return response()->json(
                AuditLogResource::collection(Client::find($clientId)->auditTrail())
            );
        }

        if(filled($caregiverId)){
            return response()->json(
                AuditLogResource::collection(Caregiver::find($caregiverId)->auditTrail())
            );
        }

        abort(404);
        return response()->json([]);
    }
}
