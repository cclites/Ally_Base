<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\PhoneController;
use App\Responses\ErrorResponse;
use Illuminate\Http\Request;

class CaregiverController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $caregivers = $this->business()->caregivers()->with(['user', 'addresses', 'phoneNumbers'])->get();
        return view('business.caregivers.index', compact('caregivers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function show(Caregiver $caregiver)
    {
        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

//        $caregiver->load(['user', 'addresses', 'phoneNumbers']);
        $schedules = $caregiver->schedules()->get();

        return view('business.caregivers.show', compact('caregiver', 'schedules'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function edit(Caregiver $caregiver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Caregiver $caregiver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Caregiver  $caregiver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caregiver $caregiver)
    {
        //
    }

    public function address(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new AddressController())->update($request, $caregiver->user, $type, 'The caregiver\'s address');
    }

    public function phone(Request $request, $caregiver_id, $type)
    {
        $caregiver = Caregiver::findOrFail($caregiver_id);

        if (!$this->hasCaregiver($caregiver->id)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        return (new PhoneController())->update($request, $caregiver->user, $type, 'The caregiver\'s phone number');
    }
}
