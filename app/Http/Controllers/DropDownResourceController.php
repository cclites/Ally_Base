<?php

namespace App\Http\Controllers;

use App\Billing\Payer;
use App\Caregiver;
use App\Client;

use App\Http\Controllers\Business\BaseController;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use Illuminate\Http\Request;

class DropDownResourceController extends BaseController

{
    public function clients(Request $request){
        $clients = new ClientDropdownResource(Client::forBusinesses([$request->business])->active()->get());
        return response()->json($clients);
    }

    public function caregivers(Request $request){
        $caregivers = new CaregiverDropdownResource(Caregiver::forBusinesses([$request->business])->active()->get());
        return response()->json($caregivers);
    }

    public function payers(Request$request){
        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->get());
        return response()->json($payers);
    }

}

