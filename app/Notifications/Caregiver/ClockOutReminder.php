<?php

namespace App\Notifications\Caregiver;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class ClockOutReminder extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'Clock-Out Reminder - Friendly Reminder to clock-out if 20 minutes past end time';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'REMINDER: Your shift with Client #CLIENT# was supposed to end at #TIMESTAMP# but you have not clocked-out yet.  Please clock-out.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'Clock Out';

    /**
     * The related shift.
     *
     * @var \App\Shift
     */
    public $shift;

    /**
     * Create a new notification instance.
     *
     * @var \App\Shift $shift
     * @return void
     */
    public function __construct($shift)
    {
        $this->shift = $shift;
        $this->url = route('clock_out');
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $timestamp = local_date($this->shift->getEndDateTime(), 'm/d/Y g:iA', $this->shift->business->timezone);

        $message = str_replace('#CLIENT#', $this->shift->client->name, static::MESSAGE);
        return str_replace('#TIMESTAMP#', $timestamp, $message);
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SendTextMessage
     * @throws \Exception
     */
    public function toSms($notifiable)
    {
        return $this->toSmsFromBusiness($notifiable, $this->shift->business);
    }
}
