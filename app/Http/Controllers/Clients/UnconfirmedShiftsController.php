<?php

namespace App\Http\Controllers\Clients;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reports\UnconfirmedShiftsReport;
use App\Shift;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shifts\ShiftStatusManager;
use App\Shifts\AllyFeeCalculator;

class UnconfirmedShiftsController extends Controller
{
    /**
     * Get a listing of all of the clients unconfirmed shifts.
     *
     * @param UnconfirmedShiftsReport $report
     * @return \Illuminate\Http\Response
     */
    public function index(UnconfirmedShiftsReport $report)
    {
        if (! auth()->user()->role->business->allow_client_confirmations) {
            return redirect('/');
        }

        $shifts = $report->forClient(auth()->id())
            ->rows();

        if (request()->wantsJson() && request()->has('json')) {
            return response()->json($shifts);
        }

        $activities = auth()->user()->role->business->allActivities();

        return view('clients.unconfirmed-shifts', compact(['shifts', 'activities']));
    }

    /**
     * Confirm the given shift.
     *
     * @param Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function confirm(Shift $shift)
    {
        if (! auth()->user()->role->business->allow_client_confirmations) {
            return redirect('/');
        }

        if (auth()->id() != $shift->client_id) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if (in_array($shift->status, ShiftStatusManager::getUnconfirmedStatuses())) {
            $shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
        }

        return new SuccessResponse('Shift has been confirmed.');
    }

    /**
     * Update the given shift.
     *
     * @param Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shift $shift)
    {
        if ($shift->client_id != auth()->id()) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        $data = $request->validate(
            [
                'checked_in_time' => 'required|date',
                'checked_out_time' => 'required|date|after_or_equal:' . $request->input('checked_in_time'),
            ],
            [
                'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.',
            ]
        );

        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);

        if ($shift->update($data)) {
            $shift->activities()->sync($request->input('activities', []));

            return new SuccessResponse('You have successfully updated this shift.');
        }
        
        return new ErrorResponse(500, 'The shift could not be updated.');
    }
    
    public function show(Request $request, Shift $shift)
    {
        if ($shift->client_id != auth()->id()) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if (! in_array($shift->status, ShiftStatusManager::getUnconfirmedStatuses())) {
            return new ErrorResponse(404, 'Unconfirmed Shift Not Found.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'caregiver', 'signature', 'statusHistory', 'goals', 'questions']);
        $shift->append(['ally_pct', 'charged_at', 'confirmed_at']);

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
            'address' => optional($shift->address)->only(['latitude', 'longitude']),
        ];

        return response()->json($data);
    }

    /**
     * Get payment info for the current client.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentType()
    {
        $client = auth()->user()->role;

        return response()->json([
            'payment_type' => $client->getPaymentType(),
            'percentage_fee' => AllyFeeCalculator::getPercentage($client)
        ]);
    }
}
