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
        $shift->load(['activities', 'issues', 'schedule', 'client', 'caregiverSignature', 'clientSignature', 'statusHistory']);

        // Load shift data into array before loading client info
        $data = $shift->toArray();

        $data += [
            'client_name' => $shift->client->name(),
            'caregiver_name' => $shift->caregiver->name(),
        ];

        return response()->json($data);
    }

}
