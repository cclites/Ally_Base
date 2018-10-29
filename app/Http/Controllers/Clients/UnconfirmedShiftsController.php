<?php

namespace App\Http\Controllers\Clients;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Reports\UnconfirmedShiftsReport;
use App\Shift;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shifts\ShiftStatusManager;

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
    public function update(Shift $shift)
    {
    }
    
}
