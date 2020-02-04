<?php

namespace App\Notifications\Business;

use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;
use App\Schedule;

class OpenShiftRequested extends BaseNotification
{
    const DISABLED = false;

    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'Get alerted when a caregiver applies for an open shift';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Shift Requested! #DATE#-#CLNAME# in zip #ZIP# for #CGNAME#';

    /**
     * The related schedule.
     *
     * @var \App\Schedule
     */
    protected $schedule;

    /**
     * The related caregiver.
     *
     * @var \App\Caregiver
     */
    protected $caregiver;

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Schedule';

    /**
     * Create a new notification instance.
     *
     * @var \App\Schedule $schedule
     * @return void
     */
    public function __construct( $schedule, $caregiver )
    {
        parent::__construct();
        $this->schedule = $schedule;
        $this->caregiver = $caregiver;
        $this->url = route( 'business.schedule.index', ['client' => $this->schedule->client]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $message = str_replace( '#DATE#', $this->schedule->getStartDateTime()->format( 'm/d@g:iA' ), static::MESSAGE );
        $message = str_replace( '#CLNAME#', $this->schedule->client->initialedName, $message );
        $message = str_replace( '#CGNAME#', $this->caregiver->initialedName, $message );
        return str_replace( '#ZIP#', $this->schedule->client->evvAddress->zip ?? null, $message );
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
        return $this->toSmsFromBusiness( $notifiable, $this->client->business );
    }

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SystemNotification
     */
    public function toSystem( $notifiable, $data = [] )
    {
        return parent::toSystem( $notifiable, [
            'reference_id' => $this->schedule->id,
            'reference_type' => Schedule::class
        ]);
    }
}
