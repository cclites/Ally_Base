<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\CaregiverLicense;
use App\Notifications\LicenseExpirationReminder;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use Log;

class CaregiverLicenseController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Caregiver $caregiver)
    {
        $this->authorize('read', $caregiver);

        return $caregiver->licenses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Caregiver $caregiver)
    {
        $this->authorize('update', $caregiver);
        $this->addNewExpirationType($request->all());

        $data = $request->validate([
            'name' => 'required|max:200',
            'description' => 'nullable',
            'expires_at' => 'required|date',
        ]);

        $data['expires_at'] = filter_date($data['expires_at']);

        $license = new CaregiverLicense($data);
        if ($caregiver->licenses()->save($license)) {
            return new SuccessResponse('The license has been added.', $license->toArray());
        }
        return new ErrorResponse(500, 'The license could not be saved.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CaregiverLicense  $license
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Caregiver $caregiver, CaregiverLicense $license)
    {
        $this->authorize('update', $caregiver);
        $this->addNewExpirationType($request->all());

        $data = $request->validate([
            'name' => 'required|max:200',
            'description' => 'nullable',
            'expires_at' => 'required|date',
        ]);

        $data['expires_at'] = filter_date($data['expires_at']);

        if ($license->update($data)) {
            return new SuccessResponse('The license has been updated.', $license->toArray());
        }
        return new ErrorResponse(500, 'The license could not be updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CaregiverLicense  $license
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caregiver $caregiver, CaregiverLicense $license)
    {
        $this->authorize('update', $caregiver);

        if ($license->delete()) {
            return new SuccessResponse('The license has been deleted.');
        }
        return new ErrorResponse(500, 'The license could not be deleted.');
    }

    public function expirationReminder(CaregiverLicense $license)
    {
        $this->authorize('read', $license->caregiver);

        $license->caregiver->user->notify(new LicenseExpirationReminder($this->business(), $license));
    }

    public function addNewExpirationType($request){

        $business = \App\Business::find($request["business_id"])->first();

        //make sure we dont re-add a default type.
        $defaultTypes = \App\ExpirationTypes::where('type', $request["name"])
                        ->whereNull('chain_id')
                        ->get();

        if(!$defaultTypes->isEmpty()){
            return;
        }else{
            $type = \App\ExpirationTypes::where('type', $request["name"])
                                          ->where('chain_id', $business->chain_id)
                                          ->where('business_id', $business->id)
                                          ->get();

            if($type->isEmpty()){
                $newType = new \App\ExpirationTypes();
                $newType->type = $request["name"];
                $newType->chain_id = $business->chain_id;
                $newType->business_id = $request["business_id"];
                $newType->save();
            }
        }

        return;

    }
}
