<?php

namespace App\Http\Controllers;

use App\Billing\Payer;
use App\Caregiver;
use App\Client;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Http\Resources\SalespersonDropdownResource;
use DB;
use Illuminate\Http\Request;

class DropDownResourceController
{
    public function clients(Request $request){
        $clients = new ClientDropdownResource(Client::forBusinesses([$request->business])->active()->get());
        return response()->json($clients);
    }

    public function caregivers(Request $request){
        $caregivers = new CaregiverDropdownResource(Caregiver::forBusinesses([$request->business])->active()->get());
        return response()->json($caregivers);
    }

    public function payers(Request $request){
        $payers = new PayersDropdownResource(Payer::forAuthorizedChain()->get());
        return response()->json($payers);
    }

    public function salespeople(Request $request){
        $salespeople = DB::table('sales_people')->where('business_id', $request->business)->get();
        return response()->json( new SalespersonDropdownResource($salespeople) );
    }

    public function marketingClients(Request $request){
        $clients = Client::forBusinesses([$request->business])->active()->whereNotNull('sales_person_id')->get();
        return response()->json(new ClientDropdownResource($clients));

    }
}