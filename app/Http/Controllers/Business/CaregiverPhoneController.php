<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Responses\ErrorResponse;

class CaregiverPhoneController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Http\Response
     */
    public function index(Caregiver $caregiver)
    {
        if (! $this->businessHasCaregiver($caregiver)) {
            return new ErrorResponse(403, 'You do not have access to this caregiver.');
        }

        $numbers = $caregiver->phoneNumbers->sort(function($a, $b) {
            $numberGen = function($type) {
                switch($type) {
                    case 'primary':
                        return 10;
                    default:
                        return 1;
                }
            };
            $aNumber = $numberGen($a->type);
            $bNumber = $numberGen($b->type);
            if ($aNumber > $bNumber) return -1;
            return ($aNumber < $bNumber) ? 1 : 0;
        });
        return response($numbers->values());
    }
}
