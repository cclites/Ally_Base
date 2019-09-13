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
use App\Events\ShiftFlagsCouldChange;

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
        if (! app('settings')->get(auth()->user()->role->business_id, 'allow_client_confirmations')) {
            return redirect('/');
        }

        $shifts = $report->forClient(auth()->id())
            ->includeConfirmed()
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
    public function confirm(Shift $shift, Request $request)
    {
        $this->authorize('update', $shift);

        if (! app('settings')->get(auth()->user()->role->business_id, 'allow_client_confirmations')) {
            return new ErrorResponse(400, 'Client confirmations are not permitted.  Please contact the registry.');
        }

        if ($request->confirmed) {
            $availableStatuses = ShiftStatusManager::getUnconfirmedStatuses();
            $newStatus = Shift::WAITING_FOR_AUTHORIZATION;
            $verb = 'confirmed';
        }
        else {
            $availableStatuses = ShiftStatusManager::getConfirmedStatuses(true);
            $newStatus = Shift::WAITING_FOR_CONFIRMATION;
            $verb = 'unconfirmed';
        }

        if (in_array($shift->status, $availableStatuses)) {
            $shift->update([
                'status' => $newStatus,
                'client_confirmed' => (bool) $request->confirmed,
            ]);
        }
        else {
            return new ErrorResponse(400, "This shift is not able to be $verb at this time.");
        }

        return new SuccessResponse("This shift has been $verb.");
    }

    /**
     * Update the given shift.
     *
     * @param \Illuminate\Http\Request $request
     * @param Shift $shift
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shift $shift)
    {
        $this->authorize('update', $shift);

        if ($shift->isReadOnly()) {
            return new ErrorResponse(400, 'This shift is locked for modification.');
        }

        if (! app('settings')->get(auth()->user()->role->business_id, 'allow_client_confirmations')) {
            return redirect('/');
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

        // Automatically confirm visits that clients modify
        if (app('settings')->get(auth()->user()->role->business_id, 'auto_confirm_modified') 
            && in_array($shift->status, ShiftStatusManager::getUnconfirmedStatuses())) {
                $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
                $data['client_confirmed'] = true;
        }

        if ($shift->update($data)) {
            $shift->activities()->sync($request->input('activities', []));
            event(new ShiftFlagsCouldChange($shift));
            return new SuccessResponse('You have successfully updated this shift.');
        }

        return new ErrorResponse(500, 'The shift could not be updated.');
    }

    /**
     * Show the shift details.
     *
     * @param Request $request
     * @param Shift $shift
     * @return ErrorResponse|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Shift $shift)
    {
        $this->authorize('read', $shift);

        if ($shift->client_id != auth()->id()) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        if (! in_array($shift->status, ShiftStatusManager::getUnconfirmedStatuses())) {
            return new ErrorResponse(404, 'Unconfirmed Shift Not Found.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'caregiver', 'clientSignature', 'caregiverSignature', 'statusHistory', 'goals', 'questions']);
        $shift->append(['ally_pct', 'charged_at', 'confirmed_at']);

        // Load shift data into array before loading client info
        $data = $shift->toArray();

        $data += [
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
