<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;

class ChainsExpirationsController extends Controller
{
    public function index($caregiverId){

        $caregiver = \App\Caregiver::find($caregiverId);
        $chainIds = $caregiver->getChainIds();

        $query = \App\ChainExpiration::select()->whereNull('chain_id');

        if(!empty($chainIds)){
            $query->whereIn('chain_id', $chainIds, 'or');
        }

        $types = $query->orderBy('type')->get()->toArray();
        return response()->json($types);

    }
}
