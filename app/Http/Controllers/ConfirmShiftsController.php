<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShiftConfirmation;
use App\ShiftStatusHistory;
use App\Shift;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;
use App\Reports\UnconfirmedShiftsReport;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConfirmShiftsController extends Controller
{
    /**
     * Confirm shifts from shift confirmation token.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmToken(Request $request, $token, UnconfirmedShiftsReport $report)
    {
        if (! $token = ShiftConfirmation::findToken($token)) {
            throw new ModelNotFoundException("Invalid Confirmation Token");
        }

        $confirmed = false;
        
        if (empty($token->confirmed_at)) {
            foreach ($token->shifts as $shift) {
                if (in_array($shift->status, ShiftStatusManager::getUnconfirmedStatuses())) {
                    $shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
                }
            }
    
            $token->update(['confirmed_at' => Carbon::now()]);
            $confirmed = true;
        }

        $unconfirmedShifts = $report
            ->forClient($token->client_id)
            ->rows();

        $total = $unconfirmedShifts->sum('total');
        return view('shift-confirmation.thank-you', compact(['token', 'confirmed', 'unconfirmedShifts', 'total']));
    }

    /**
     * Confirm all unconfirmed shifts related to the client who owns the token.
     *
     * @return \Illuminate\Http\Response
     */
    public function confirmAllWithToken(Request $request, $token, UnconfirmedShiftsReport $report)
    {
        if (! $token = ShiftConfirmation::findToken($token)) {
            throw new ModelNotFoundException("Invalid Confirmation Token");
        }

        $shifts = $token->client->shifts()
            ->where('status', Shift::WAITING_FOR_CONFIRMATION)
            ->get()
            ->each(function ($shift) {
                $shift->update(['status' => Shift::WAITING_FOR_AUTHORIZATION]);
            });

        $confirmed = true;
        $unconfirmedShifts = [];
        $total = 0;
        return view('shift-confirmation.thank-you', compact(['token', 'confirmed', 'unconfirmedShifts', 'total']));
    }
}
