<?php

namespace App\Listeners;

use App\Events\SmsThreadReplyCreated;
use Carbon\Carbon;
use App\Events\SmsThreadReply;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;
use App\BusinessCommunications;

use Log;


class HandleSmsAutoReply extends BaseNotification{


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    /**
     * Handle the event.
     *
     * @param  SmsThreadReplyCreated  $event
     * @return void
     */
    public function handle(SmsThreadReplyCreated $event)
    {
        $to = $event->reply->to_number;
        $toNumber = "(".substr($to, 0, 3).") ".substr($to, 3, 3)."-".substr($to,6);

        $now = Carbon::now();

        $query = BusinessCommunications::whereHas('business', function($q) use ($toNumber){
                    $q->where('phone1', $toNumber );
                })
                ->where('auto_off', false)
                ->orWhere('on_indefinitely', true);

        if($now->isWeekday()){
            $query->whereBetween($now, ['week_start', 'week_end']);
        }else{
            $query->whereBetween($now, ['weekend_start', 'weekend_end']);
        }

        $settings = $query->get();

        Log::info(json_encode($settings));

    }
}

?>