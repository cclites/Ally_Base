<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;

class ExpirationTypesController extends BaseController
{
    public function index(Request $request){

        $chain_id = $this->businessChain()->id;

        $types =  \App\ExpirationTypes::whereNull('chain_id')
                            ->orWhere('chain_id', $chain_id)
                            //->orderBy('type')
                            ->get()
                            ->toArray();

        return response()->json($types);

    }
}
