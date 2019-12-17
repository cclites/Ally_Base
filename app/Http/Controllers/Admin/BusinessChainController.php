<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\BusinessChain;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidTimezoneOrOffset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PhoneNumber;

class BusinessChainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $chains = BusinessChain::ordered()->get();

        if ($request->expectsJson()) {
            return $chains;
        }

        return view('admin.businesses.chains', compact('chains'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Http\Response
     */
    public function show(BusinessChain $chain)
    {
        $chain->load('clientTypeSettings');

        return view('admin.businesses.show_chain', compact('chain'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BusinessChain  $chain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BusinessChain $chain)
    {
        $chainData = $request->validate([
            'name' => 'required',
            'address1' => 'string|nullable',
            'city' => 'string|nullable',
            'state' => 'string|nullable',
            'zip' => 'string|nullable',
            'phone1' => 'string|nullable',
            'calendar_week_start' => 'required|in:0,1,2,3,4,5,6',
        ]);

        if ($chain->update($chainData)) {
            return new SuccessResponse('The chain has been saved.');
        }
    }
}
