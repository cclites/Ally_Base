<?php

namespace App\Http\Controllers\Business;

use App\ExpirationType;
use Illuminate\Http\Request;
use App\Responses\SuccessResponse;

class ExpirationTypesController extends BaseController
{
    /**
     * Get a list of the Chain's default expiration types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ExpirationType::where('chain_id', $this->businessChain()->id);

        if(!$request->has('manage')){
            $query->orWhereNull('chain_id');
        }

        return response()->json(
            $query->orderBy('type')
                ->get()
                ->values()
        );
    }

    /**
     * Store a new expiration type and return an updated list in the response
     * @param $type
     * @return SuccessResponse
     */
    public function store($type){
        $chainId = $this->businessChain()->id;
        $expirationType = new ExpirationType;
        $expirationType->type = $type;
        $expirationType->chain_id = $chainId;
        $expirationType->save();

        $types = ExpirationType::where('chain_id', $chainId)->get();
        return new SuccessResponse('Added default expiration type', $types);
        //return response()->json($types);
    }

    /**
     * Destroy a default type and return an updated list in the response
     *
     * @param $typeId
     * @return SuccessResponse
     */
    public function destroy($id){
        $chainId = $this->businessChain()->id;
        ExpirationType::destroy($id);
        $types = ExpirationType::where('chain_id', $chainId)->get();
        return new SuccessResponse('Removed default expiration type', $types);
        //return response()->json($types);
    }
}
