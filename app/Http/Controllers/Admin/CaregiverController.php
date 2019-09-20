<?php

namespace App\Http\Controllers\Admin;

use App\Caregiver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CaregiverController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     */
    public function index(Request $request)
    {
        $caregivers = Caregiver::query()->active()->orderByName();

        if(filled($request->id)){
            $caregivers->forRequestedBusinesses([$request->id]);
        }

        return response($caregivers->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     */
    public function store(Request $request){}

    /**
     * Display the specified resource.
     * 
     * @param Caregiver $caregiver
     */
    public function show(Caregiver $caregiver){}

    /**
     * Show the form for editing the specified resource.
     * 
     * @param Caregiver $caregiver
     */
    public function edit(Caregiver $caregiver){}

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param Caregiver $caregiver
     */
    public function update(Request $request, Caregiver $caregiver){}

    /**
     * Remove the specified resource from storage.
     * 
     * @param Caregiver $business
     */
    public function destroy(Caregiver $caregiver){}
}
