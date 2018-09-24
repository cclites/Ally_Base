<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Caregiver;
use App\Jobs\SendTextMessage;
use App\User;
use App\Responses\ErrorResponse;
use App\Shift;
use App\Schedule;
use App\Traits\ActiveBusiness;

class CommunicationController extends Controller
{
    use ActiveBusiness;

    /**
     * Show text-caregivers form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createText()
    {
        $message = '';

        if (request()->preset == 'open-shift' && request()->has('shift_id')) {
            $shift = Schedule::findOrFail(request()->shift_id);

            if (! $this->businessHasSchedule($shift)) {
                return new ErrorResponse(403, 'You do not have access to this shift.');
            }

            $clientName = $shift->client->name;
            $date = $shift->starts_at->format('m/d/y');
            $time = $shift->starts_at->format('g:i A');
            
            $location = '';
            if ($shift->client->evvAddress) {
                $location = $shift->client->evvAddress->city . ', ' . $shift->client->evvAddress->zip;
            }
            $registryName = $shift->business->name;
            $phone = $shift->business->phone1;
            
            $message = "Shift Available\r\n$clientName / $date @ $time / $location\r\n\r\nPlease call $registryName if interested.  First come, first serve. $phone";
        }

        return view('business.communication.text-caregivers', compact(['message']));
    }

    /**
     * Initiate SMS text blast.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendText(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:5',
            'recipients' => 'array',
            'recipients.*' => 'integer',
        ]);

        if ($request->all) {
            $recipients = User::where('role_type', 'caregiver')
                ->where('active', 1)
                ->has('phoneNumbers')
                ->with('phoneNumbers')
                ->get();
        } else {
            $recipients = User::whereIn('id', $request->recipients)
                ->has('phoneNumbers')
                ->with('phoneNumbers')
                ->get();

            if ($recipients->count() == 0) {
                return new ErrorResponse(422, 'You must have at least 1 recipient.');
            }
        }

        // if empty, will automatically use twilio default
        $from = activeBusiness()->outgoing_sms_number;

        // send txt to all primary AND mobile numbers
        foreach($recipients as $recipient) {
            if ($number = $recipient->phoneNumbers->where('type', 'primary')->first()) {
                dispatch(new SendTextMessage($number->national_number, $request->message, $from));
            }

            if ($number = $recipient->phoneNumbers->where('type', 'mobile')->first()) {
                dispatch(new SendTextMessage($number->national_number, $request->message, $from));
            }
        }

        return new SuccessResponse('Text messages were successfully dispatched.');
    }
}
