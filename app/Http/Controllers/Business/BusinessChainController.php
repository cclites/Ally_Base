<?php

namespace App\Http\Controllers\Business;

use App\BusinessChain;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class BusinessChainController extends BaseController
{
    /**
     * Should this be the show function?
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json( $this->businessChain() );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BusinessChain  $chain
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, BusinessChain $chain_setting )
    {
        $chainData = $request->validate([

            'open_shifts_setting' => 'required|string',
        ]);

        if( $chain_setting->update( $chainData ) ) return new SuccessResponse( 'The Chain has been updated.' );
        else return new ErrorResponse( 500, "Unable to update Chain Settings." );
    }
}
