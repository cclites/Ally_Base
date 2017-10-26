<?php

namespace App\Http\Controllers\Business;

use App\Events\UnverifiedShiftApproved;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shift;

class ShiftController extends BaseController
{
    public function show($shift_id)
    {
        $shift = Shift::with(['activities', 'issues'])->findOrFail($shift_id);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Include distances
        $data = $shift->toArray();
        if ($address = $shift->client->evvAddress) {
            if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                $data['checked_in_distance'] = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
            }
            if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                $data['checked_out_distance'] = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
            }
        }

        $data['client_name'] = $shift->client->name();
        $data['caregiver_name'] = $shift->caregiver->name();

        return $data;
    }

    public function verify($shift_id)
    {
        $shift = Shift::with(['activities', 'issues'])->findOrFail($shift_id);
        if ($this->business()->id != $shift->business_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->update(['verified' => true])) {
            event(new UnverifiedShiftApproved($shift));
            return new SuccessResponse('The shift has been verified');
        }

        return new ErrorResponse('The shift could not be verified');
    }
}
