<?php

namespace App\Notifications\Caregiver;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class ClockInReminder extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'Clock-In Reminder - Friendly Reminder to clock-in if 20 minutes past start time';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'REMINDER: Your shift with Client #CLIENT# was supposed to start at #TIMESTAMP# but you have not clocked-in yet.  Please clock-in.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'Clock In';

    /**
     * The related schedule.
     *
     * @var \App\Schedule
     */
    protected $schedule;

    /**
     * Create a new notification instance.
     *
     * @var \App\Schedule $schedule
     * @return void
     */
    public function __construct($schedule)
    {
        $this->schedule = $schedule;
        $this->url = route('clock_in');
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $timestamp = local_date($this->schedule->starts_at, 'm/d/Y g:iA', $this->schedule->business->timezone);

        $message = str_replace('#CLIENT#', $this->schedule->client->name, static::$message);
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
        return $this->toSmsFromBusiness($notifiable, $this->schedule->business);
    }
}
