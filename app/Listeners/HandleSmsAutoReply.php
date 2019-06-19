<?php

namespace App\Listeners;

use App\Events\SmsThreadReplyCreated;
use Carbon\Carbon;
use App\Events\SmsThreadReply;
use App\Jobs\SendTextMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class HandleSmsAutoReply
 * @package App\Listeners
 */
class HandleSmsAutoReply implements ShouldQueue
{
    /**
     * @var SmsThreadReplyCreated
     */
    private $event;

    /**
     * Handle the event.
     *
     * @param SmsThreadReplyCreated $event
     * @return void
     */
    public function handle(SmsThreadReplyCreated $event)
    {
        $this->event = $event;

        if (empty($event->reply->business)) {
            // SMS did not match any business numbers in the system,
            // so there are no auto reply settings to check.
            return;
        }

        if ($message = $this->getAutoReply()) {
            dispatch(new SendTextMessage($event->reply->from_number, $message, $event->reply->business->outgoing_sms_number));
        }
    }

    /**
     * Check for message to send auto-reply.
     *
     * @return string|null
     */
    public function getAutoReply() : ?string
    {
        $settings = $this->event->reply->business->communicationSettings;
        $timezone = $this->event->reply->business->timezone ?: 'America/New_York';

        if ($settings->selected === 'off') {
            return null;
        } else if ($settings->selected === 'on') {
            return $settings->message;
        }

        // Check scheduled times.
        $now = Carbon::now()->setTimezone($timezone);
        if ($now->isWeekday()) {
            $start = Carbon::parse($now->toDateString() . ' ' . $settings->week_start, $timezone);
            $end = Carbon::parse($now->toDateString() . ' ' . $settings->week_end, $timezone);
        } else if ($now->isWeekend()) {
            $start = Carbon::parse($now->toDateString() . ' ' . $settings->weekend_start, $timezone);
            $end = Carbon::parse($now->toDateString() . ' ' . $settings->weekend_end, $timezone);
        } else {
            return null;
        }

        if ($now->greaterThan($start) || $now->lessThan($end)) {
            return $settings->message;
        }

        return null;
    }
}