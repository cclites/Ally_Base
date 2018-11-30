<?php

namespace App\Http\Controllers\Admin;

use App\Caregiver;
use App\Business;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusinessCaregiverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function index(Business $business)
    {
        return $business->chain
            ->caregivers()
            ->ordered()
            ->active()
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function create(Business $business)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Business  $business
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Business $business)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Business  $business
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function show(Business $business, Caregiver $caregiver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Business  $business
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function edit(Business $business, Caregiver $caregiver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Business  $business
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Business $business, Caregiver $caregiver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Business  $business
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Business $business, Caregiver $caregiver)
    {
        //
    }
}
