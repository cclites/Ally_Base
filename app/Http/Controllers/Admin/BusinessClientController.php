<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Business;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusinessClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Business $business
     * @return \Illuminate\Http\Response
     */
    public function index(Business $business)
    {
        return User::whereRoleType('client')
                   ->whereActive(1)
                   ->whereIn('id', function ($q) use ($business) {
                       $q->select('id')
                         ->from('clients')
                         ->where('business_id', $business->id);
                   })
                   ->orderBy('lastname')
                   ->orderBy('firstname')
                   ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Business $business
     * @return \Illuminate\Http\Response
     */
    public function create(Business $business)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Business $business
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Business $business)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Business $business
     * @param  \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function show(Business $business, Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Business $business
     * @param  \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Business $business, Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Business $business
     * @param  \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Business $business, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Business $business
     * @param  \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Business $business, Client $client)
    {
        //
    }
}
