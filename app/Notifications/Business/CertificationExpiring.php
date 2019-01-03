<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class CertificationExpiring extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = '30 Days Before a Caregiver Certification Expires';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'Caregiver #CAREGIVER# - #CERTNAME# expires in 30 days.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Caregiver Profile';

    /**
     * The related shift.
     *
     * @var \App\CaregiverLicense
     */
    protected $license;

    /**
     * Create a new notification instance.
     *
     * @var \App\CaregiverLicense $license
     * @return void
     */
    public function __construct($license)
    {
        $this->license = $license;
        $this->url = route('business.caregivers.show', ['caregiver' => $this->license->caregiver]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $message = str_replace('#CAREGIVER#', $this->license->caregiver->name, static::$message);
        $message = str_replace('#CERTNAME#', $this->license->name, $message);
        return $message;
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
        // TODO: handle sending to all business chains the caregiver belongs to
    }
}
