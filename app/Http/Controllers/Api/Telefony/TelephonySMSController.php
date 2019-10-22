<?php
namespace App\Http\Controllers\Api\Telefony;

use App\Business;
use App\PhoneNumber;
use App\SmsThread;
use App\SmsThreadRecipient;
use App\SmsThreadReply;
use Illuminate\Http\Request;

class TelephonySMSController extends BaseTelefonyController
{
    /**
     * Handle incoming SMS (replies)
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function incoming(Request $request)
    {
        if (!$this->authorizeRequest($request)) {
            return $this->unauthorizedResponse();
        }

        $validator = \Validator::make($request->all(), [
            'MessageSid' => 'required|string',
            'To' => 'required|string',
            'From' => 'required|string',
            'Body' => 'required_without:MediaUrl',
            'MediaUrl' => 'required_without|Body'
        ]);

        if ($validator->fails()) {
            return $this->xmlResponse('<error>' . $validator->errors()->first() . '</error>');
        }

        // ignore duplicate requests
        if (SmsThreadReply::where('twilio_message_id', $request->MessageSid)->exists()) {
            return $this->xmlResponse('<notice>Duplicate request ignored.</notice>');
        }

        $to = PhoneNumber::formatNational($request->To);
        $from = PhoneNumber::formatNational($request->From);
        $thread = SmsThread::where('from_number', $to)->latest()->first();
        $matchingPhone = PhoneNumber::where('national_number', $from)->first();
        $user_id = optional($matchingPhone)->user_id;

        if (empty($thread)) {
            $business = Business::where('outgoing_sms_number', $to)->first();
            $business_id = optional($business)->id;
        } else {
            $business_id = $thread->business_id;
            if (! $thread->isAcceptingReplies() || ! $thread->hasRecipient($user_id)) {
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
            'twilio_message_id' => $request->MessageSid,
        ]);

        if (! $reply) {
            return $this->xmlResponse('<error>Failed to record reply</error>', 500);
        }

        // Empty twiml response for now
        return $this->telefony->response();
    }
}