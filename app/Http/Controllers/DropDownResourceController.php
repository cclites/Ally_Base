<?php

namespace App\Http\Controllers;

use App\Billing\Payer;
use App\Caregiver;
use App\Client;
use App\Http\Resources\ClientDropdownResource;
use App\Http\Resources\CaregiverDropdownResource;
use App\Http\Resources\PayersDropdownResource;
use App\Http\Resources\SalespersonDropdownResource;
use App\SalesPerson;
use Illuminate\Http\Request;
use DB;

use Log;

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
    }I wou

    public function salespeople(Request $request){
        $salespeople = DB::table('sales_people')->where('business_id', $request->business)->get();
        return response()->json( new SalespersonDropdownResource($salespeople) );
    }

    public function marketingClients(Request $request){

        /*
        $clientIds = [];

        SalesPerson::query()
            ->where('business_id', $request->business)
            ->get()
            ->map(function(SalesPerson $salesperson) use(&$clientIds){
                $clientIdVals = $salesperson->clientIds();
                foreach ($clientIdVals as $clientId){
                    $clientIds[] = $clientId;
                }
                return $salesperson;
            });
        */
        $clients = Client::forBusinesses([$request->business])->active()->whereNotNull('sales_person_id')->get();

        //dd($clients);

        foreach($clients as $client){
            Log::info($client->sales_person_id);
        }


        //$salesClients = new ClientDropdownResource($clients);
        //return response()->json($salesClients);

        return [];
    }
}