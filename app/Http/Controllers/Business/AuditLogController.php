<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Caregiver;
use App\Client;
use App\Shift;

class AuditLogController extends Controller
{
    public function show(Request $request){
        $smsId = $request->sms_id;
        $shiftId = $request->shift_id;
        $clientId = $request->client_id;
        $caregiverId = $request->caregiver_id;

        if(filled($smsId)){
            return response()->json(SmsThread::find($smsId)->auditTrail());
        }

        if(filled($shiftId)){
            return response()->json(Shift::find($shiftId)->auditTrail());
        }

        if(filled($clientId)){
            return response()->json(Client::find($clientId)->auditTrail());
        }

        if(filled($caregiverId)){
            return response()->json(Caregiver::find($caregiverId)->auditTrail());
        }
    }
}
