<?php

namespace App\Http\Controllers\Caregivers;

use App\Responses\ErrorResponse;
use App\Shift;

class ShiftController extends BaseController
{

    /**
     * Returns single shift details for report details modal.
     *
     * @param Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shift $shift)
    {
        if ($shift->caregiver_id != $this->caregiver()->id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'signature', 'statusHistory']);

        // Load shift data into array before loading client info
        $data = $shift->toArray();

        // Calculate distances
        $checked_in_distance = null;
        $checked_out_distance = null;
        if ($address = $shift->client->evvAddress) {
            if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                $checked_in_distance = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
            }
            if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                $checked_out_distance = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
            }
        }

        $data += [
            'checked_in_distance' => $checked_in_distance,
            'checked_out_distance' => $checked_out_distance,
            'client_name' => $shift->client->name(),
            'caregiver_name' => $shift->caregiver->name(),
        ];

        return response()->json($data);
    }

}
