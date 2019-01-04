<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\Schedule;

class CaregiverAvailable extends BaseNotification
{
    public static $disabled = true;
    
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'Caregiver Marks Themselves Available for an Open Shift';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'Caregiver #CAREGIVER# is available to work for Client #CLIENT# #TIMESTAMP#.  Go to the Open Shifts page to see more details.';

    /**
     * The related caregiver.
     *
     * @var \App\Caregiver
     */
    protected $caregiver;

    /**
     * The related schedule.
     *
     * @var \App\Schedule
     */
    protected $schedule;

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Open Shifts';

    /**
     * Create a new notification instance.
     *
     * @var \App\Caregiver $caregiver
     * @var \App\Schedule $schedule
     * @return void
     */
    public function __construct($caregiver, $schedule)
    {
        $this->caregiver = $caregiver;
        $this->schedule = $schedule;

        $this->url = route('business.clients.schedule', ['client' => $this->schedule->client]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $timestamp = local_date($this->schedule->starts_at, 'm/d/Y g:iA', $this->schedule->business->timezone);

        $message = str_replace('#CAREGIVER#', $this->caregiver->name, static::$message);
        $message = str_replace('#CLIENT#', $this->schedule->client->name, $message);
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

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SystemNotification
     */
    public function toSystem($notifiable, $data = [])
    {
        return parent::toSystem($notifiable, [
            'reference_id' => $this->schedule->id,
            'reference_type' => Schedule::class
        ]);
    }
}
