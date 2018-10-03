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
use Illuminate\Validation\Validator;
use App\Business;
use App\SmsThread;
use App\PhoneNumber;
use App\SmsThreadReply;
use Carbon\Carbon;
use App\SmsThreadRecipient;

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
            'can_reply' => 'boolean',
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

        $business = activeBusiness();
        $from = $business->outgoing_sms_number;
        if (empty($from)) {
            if ($request->can_reply) {
                return new ErrorResponse(422, 'You cannot receive SMS replies at this time because you have not been assigned a unique outgoing SMS number, please contact Ally.');
            }

            $from = PhoneNumber::formatNational(config('services.twilio.default_number'));
        }

        $thread = SmsThread::create([
            'business_id' => $business->id,
            'from_number' => $from,
            'message' => $request->message,
            'can_reply' => $request->can_reply,
            'sent_at' => Carbon::now(),
        ]);

        // send txt to all primary AND mobile numbers
        foreach($recipients as $recipient) {
            if ($number = $recipient->phoneNumbers->where('type', 'primary')->first()) {
                dispatch(new SendTextMessage($number->number(false), $request->message, $business->outgoing_sms_number));
                $thread->recipients()->create(['user_id' => $recipient->id, 'number' => $number->national_number]);
            }

            if ($number = $recipient->phoneNumbers->where('type', 'mobile')->first()) {
                dispatch(new SendTextMessage($number->number(false), $request->message, $business->outgoing_sms_number));
                $thread->recipients()->create(['user_id' => $recipient->id, 'number' => $number->national_number]);
            }
        }

        return new SuccessResponse('Text messages were successfully dispatched.');
    }

    /**
     * Handle incoming SMS messages from Twilio webhooks.
     *
     * @return Response
     */
    public function incoming(Request $request)
    {
        $twilioSid = config('services.twilio.sid');

        if (\Validator::make($request->all(), [
            'MessageSid' => 'required|string|max:34|min:34',
            'AccountSid' => "required|string|max:34|min:34|in:$twilioSid",
            // 'MessagingServiceSid' => 'required|string|max:34|min:34',
            'To' => 'required|string',
            'From' => 'required|string',
            'Body' => 'required|string',
        ])->fails()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
    
        $to = PhoneNumber::formatNational($request->To);
        $from = PhoneNumber::formatNational($request->From);

        $thread = SmsThread::where('from_number', $to)->latest()->first();
        $business_id = optional($thread)->business_id;

        $matchingPhone = PhoneNumber::where('national_number', $from)->first();
        $user_id = optional($matchingPhone)->user_id;
 
        if (empty($thread)) {
            $business = Business::where('outgoing_sms_number', $to)->first();
            $business_id = optional($business)->id;
        } else {
            $matchingRecipient = SmsThreadRecipient::where('sms_thread_id', $thread->id)->first();
            if (! empty($matchingRecipient)) {
                $user_id = $matchingRecipient->user->id;
            }

            if (! $thread->isAcceptingReplies()) {
                $thread = null;
            }
        }

        $reply = SmsThreadReply::create([
            'business_id' => $business_id,
            'sms_thread_id' => optional($thread)->id,
            'user_id' => $user_id,
            'from_number' => $from,
            'to_number' => $to,
            'message' => $request->Body,
        ]);

        return response()->json(['status' => 200]);
    }
}
