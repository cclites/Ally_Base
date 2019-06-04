<?php

namespace App\Http\Controllers\Business\Clients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;



use Log;

class ClientCommunicationsController extends Controller
{
    public function index(Request $request){

    }

    public function show($client){
      Log::info($client);
      return \App\ClientCommunications::find($client);
    }

    public function create(Request $request, $client){

       $comms = \App\ClientCommunications::where('client_id', $client);

       Log::info(json_encode($comms));

       if($comms){
           return "Client exists. Just update";
       }else{
           return "Client does not exist. Create";
       }

    }

    public function update(Request $request){

    }

}
