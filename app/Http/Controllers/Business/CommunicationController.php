<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Caregiver;
use App\Jobs\SendTextMessage;
use App\User;
use App\Responses\ErrorResponse;

class CommunicationController extends Controller
{
    /**
     * Show sms-caregivers form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSms()
    {
        return view('business.communication.sms-caregivers');
    }

    /**
     * Initiate SMS blast.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendSms(Request $request)
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

        return new SuccessResponse('SMS messages were successfully dispatched.');
    }
}
