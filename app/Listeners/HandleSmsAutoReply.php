<?php

namespace App\Listeners;

use App\Business;
use App\Events\SmsThreadReplyCreated;
use Carbon\Carbon;
use App\Events\SmsThreadReply;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;
use App\BusinessCommunications;

use Log;

/**
 * Class HandleSmsAutoReply
 * @package App\Listeners
 */
class HandleSmsAutoReply extends BaseNotification{

    public $sendNotification = false;

    /**
     * Handle the event.
     *
     * @param  SmsThreadReplyCreated  $event
     * @return void
     */
    public function handle(SmsThreadReplyCreated $event)
    {
        $to = $event->reply->to_number;

        //Didn't use the PhoneNumber object
        $toNumber = "(".substr($to, 0, 3).") ".substr($to, 3, 3)."-".substr($to,6);
        $toNumber2 = substr($to, 0, 3)."-".substr($to, 3, 3)."-".substr($to,6);

        $this->sendResponse = false;

        $now = Carbon::now('UTC');
        $formattedTime = $now->format('G:i');

        $settings = BusinessCommunications::whereHas('business', function($q) use ($toNumber, $toNumber2){
                        $q->where('phone1', $toNumber, 'like')
                          ->orWhere('phone1', $toNumber2, 'like');
                    })
                    ->where('auto_off', false)
                    ->orWhere('on_indefinitely', true)
                    ->first();

        if(!is_null($settings)){

            $tz = Business::where('id', $settings->business_id)->select('timezone')->first();

            if($settings->on_indefinitely === true){
               $this->sendResponse = true;
            }elseif($now->isWeekday()){
               $this->checkTimeRange($settings->week_start, $settings->week_end, $formattedTime, $tz->timezone);
            }elseif($now->isWeekend()){
               $this->checkTimeRange($settings->weekend_start, $settings->weekend_end, $formattedTime, $tz->timezone);
            }

        }else{
            return;
        }

        if($this->sendResponse === true){
            dispatch(new SendTextMessage($event->reply->from_number, $settings->message));
        }

    }

    /**
     * This is a bit of a mind warp. The $end represents the time in the
     * morning when auto-replies should stop. $start represents the time
     * later in the day when auto-replies should start
     *
     * @param $start
     * @param $end
     * @param $now
     */
    public function checkTimeRange($start, $end, $now, $tz = null){

        $startTuples = explode(":", $start);
        $start = Carbon::createFromTime($startTuples[0], $startTuples[1], 00, $tz)->format('G:i');
        $endTuples = explode(":", $end);
        $end = Carbon::createFromTime($endTuples[0], $endTuples[1], 00, $tz)->format('G:i');

        $end = intval(str_replace(':', "", $end));
        $start = intval(str_replace(':', "", $start));
        $now = intval(str_replace(':', "", $now));

        if ($now > $end && $now < $start){
            $this->sendResponse = false;
        }else{
            $this->sendResponse = true;
        }


    }
}

?>