<?php

namespace App\Notifications\Business;

use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;
use App\Schedule;

class DeclinedVisit extends BaseNotification
{
    const DISABLED = true;

    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'Caregiver Declines a Visit';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Caregiver #CAREGIVER# has declined to work for Client #CLIENT# #TIMESTAMP#.';

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
    protected $action = 'View Client Schedule';

    /**
     * Create a new notification instance.
     *
     * @var \App\Schedule $schedule
     * @return void
     */
    public function __construct($schedule)
    {
        parent::__construct();
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
        $timestamp = $this->schedule->getStartDateTime()->format('m/d/Y g:iA');

        $message = str_replace('#CAREGIVER#', $this->schedule->caregiver->name, static::MESSAGE);
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
        return $this->toSmsFromBusiness($notifiable, $this->client->business);
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
